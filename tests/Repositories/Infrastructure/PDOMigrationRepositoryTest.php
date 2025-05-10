<?php

use Lucas\Tcc\Models\Domain\Collection;
use Lucas\Tcc\Models\Domain\Database\DatabaseType;
use Lucas\Tcc\Models\Domain\Migration\Migration;
use Lucas\Tcc\Models\Domain\Migration\MigrationStatus;
use Lucas\Tcc\Models\Infrastructure\PDO\DataSource\PDODataSource;
use Lucas\Tcc\Models\Infrastructure\PDO\PDODatabase;
use Lucas\Tcc\Repositories\Infrastructure\PDOMigrationRepository;
use Lucas\Tcc\Repositories\Infrastructure\PDOTreatmentRepository;
use PHPUnit\Framework\TestCase;

class PDOMigrationRepositoryTest extends TestCase
{
    private static function sqlitePDO(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("
            CREATE TABLE treatments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                parameters TEXT,
                function TEXT
            );

            CREATE TABLE databases (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                type_id INTEGER,
                name TEXT,
                config TEXT
            );

            CREATE TABLE database_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                writable INTEGER /* 1 = SIM, 0 = NAO */
            );

            CREATE TABLE collections (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                database_origin INTEGER,
                database_destiny INTEGER
            );

            CREATE TABLE migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                collection_id INTEGER,
                json TEXT,
                status INTEGER
            );
        ");
        $pdo->query('
            INSERT INTO treatments (id, name, parameters, function) VALUES
            (123, "multiplier", "$input, $multiplier", "return $input * $multiplier;");
        ');
        $pdo->query("
            INSERT INTO database_types (id, name, writable) VALUES 
            (1, 'JSON', 0),
            (2, 'SQL', 1),
            (3, 'Mongo', 1); 
        ");
        $pdo->query("
            INSERT INTO databases (id, type_id, name, config) VALUES
            (1, 1, 'Dump importado', '{\"dsn\": \"sqlite::memory:\",\"user\":\"\",\"password\":\"\"}'),
            (2, 2, 'Origem', '{\"dsn\": \"sqlite::memory:\",\"user\":\"\",\"password\":\"\"}'); 
            (3, 2, 'Destino', '{\"dsn\": \"sqlite::memory:\",\"user\":\"\",\"password\":\"\"}'); 
        ");
        $pdo->query("
            INSERT INTO collections (id, database_origin, database_destiny) VALUES
            (1, 2, 3),
            (2, 1, 2);
        ");

        $json = '{"from": {"identifier": "pessoa","clauses": [{"field": "IdInstituicao","operator": "=","value": "51"},{"field": "Excluido","operator": "=","value": "N"}],"with": null},"to": {"identifier": "pessoas","clauses": null,"with": null},"connections": [{"from": "IdPessoa","to": "id_origem","treatment": 123},{"from": "NomeRazaoSocial","to": "nome","treatment": null}]}';

        $pdo->query("
            INSERT INTO migrations (id, collection_id, json, status) VALUES
            (1, 1, '$json', 1),
            (2, 1, '$json', 1);
        ");

        return $pdo;
    }

    public static function providerMigrationRepository(): array
    {
        $pdo = self::sqlitePDO();

        return [
            [new PDOMigrationRepository(
                $pdo,
                new PDOTreatmentRepository($pdo),
            )],
        ];
    }

    private static function callPrivateProperty(
        mixed $object, 
        string $property
    ): mixed {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
    
        return $property->getValue($object);
    }

    /** @dataProvider providerMigrationRepository */
    public function testRepositoryDeveListarMigracoesDeUmaCollectionCorretamente(PDOMigrationRepository $repository): void
    {
        $databaseType = new DatabaseType(
            2,
            'SQL',
            true
        );
        $databaseConfig = [
            'dsn' => 'sqlite::memory:',
            'user' => '',
            'password' => '',
        ];

        $collection  = new Collection(
            1,
            new PDODatabase(
                2,
                $databaseType,
                'Origem',
                $databaseConfig,
            ),
            new PDODatabase(
                3,
                $databaseType,
                'Destino',
                $databaseConfig,
            ),
            null
        );

        $migrationList = $repository->listByCollection($collection);
        
        self::assertCount(
            2,
            $migrationList,
        );

        $migrationExpectedTreatmentRepository = new PDOTreatmentRepository(self::sqlitePDO());
        $migrationExpectedConnections = [
            [
                'from' => 'IdPessoa',
                'to' => 'id_origem',
                'treatment' => 123,
            ],
            [
                'from' => 'NomeRazaoSocial',
                'to' => 'nome',
                'treatment' => null,
            ],
        ];
        $migrationExpectedStatus = MigrationStatus::Created;
        $migrationExpectedList = [
            new Migration(
                1,
                $collection->getOriginDatabase()->getDataSource('pessoa', []),
                $collection->getDestinyDatabase()->getDataSource('pessoas', []),
                $migrationExpectedConnections,
                $migrationExpectedTreatmentRepository,
                $migrationExpectedStatus,
            ),
            new Migration(
                2,
                $collection->getOriginDatabase()->getDataSource('pessoa', []),
                $collection->getDestinyDatabase()->getDataSource('pessoas', []),
                $migrationExpectedConnections,
                $migrationExpectedTreatmentRepository,
                $migrationExpectedStatus,
            ),
        ];
    
        foreach ($migrationExpectedList as $index => $migrationExpected) {
            $migration = $migrationList[$index];

            self::assertEquals(
                $migrationExpected->getId(),
                $migration->getId(),
            );

            /** @var PDODataSource */
            $expectedFromDataSource = self::callPrivateProperty($migrationExpected, 'from');
            /** @var PDODataSource */
            $fromDataSource = self::callPrivateProperty($migration, 'from');

            self::assertEquals(
                $expectedFromDataSource->getName(),
                $fromDataSource->getName(),
            );

            /** @var PDODataSource */
            $expectedToDataSource = self::callPrivateProperty($migrationExpected, 'to');
            /** @var PDODataSource */
            $toDataSource = self::callPrivateProperty($migration, 'to');

            self::assertEquals(
                $expectedToDataSource->getName(),
                $toDataSource->getName(),
            );

            $expectedConnections = self::callPrivateProperty($migrationExpected, 'connections');
            $connections = self::callPrivateProperty($migration, 'connections');

            self::assertCount(
                2,
                $connections,
            );

            self::assertEqualsCanonicalizing(
                $expectedConnections,
                $connections,
            );
        }
    }

}