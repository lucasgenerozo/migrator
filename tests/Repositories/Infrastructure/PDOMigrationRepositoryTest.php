<?php

use Lucas\Tcc\Models\Domain\Collection;
use Lucas\Tcc\Models\Domain\Database\DatabaseType;
use Lucas\Tcc\Models\Domain\Migration;
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
                status INTEGER,
            );
        ");
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

        $json = addslashes('{"from": {"identifier": "pessoa","clauses": [{"field": "IdInstituicao","operator": "=","value": "51"},{"field": "Excluido","operator": "=","value": "N"}],"with": null},"to": {"identifier": "pessoas","clauses": null,"with": null},"connections": [{"from": "IdPessoa","to": "id_origem","treatment": 123},{"from": "NomeRazaoSocial","to": "nome","treatment": null}]}');

        $pdo->query("
            INSERT INTO migrations (id, collection_id, json, status) VALUES
            (1, 1, '$json', 0),
            (2, 1, '$json', 0);
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
        $migrationExpectedList = [
            new Migration(
                1,
                $collection->getOriginDatabase()->getDataSource('pessoa', []),
                $collection->getDestinyDatabase()->getDataSource('pessoas', []),
                $migrationExpectedConnections,
                $migrationExpectedTreatmentRepository,
            ),
            new Migration(
                2,
                $collection->getOriginDatabase()->getDataSource('pessoa', []),
                $collection->getDestinyDatabase()->getDataSource('pessoas', []),
                $migrationExpectedConnections,
                $migrationExpectedTreatmentRepository,
            ),
        ];
    
        foreach ($migrationExpectedList as $index => $migrationExpected) {
            $migration = $migrationList[$index];

            self::assertEquals(
                $migrationExpected->getId(),
                $migration->getId(),
            );

            self::assertEquals(
                $migrationExpected->getId(),
                $migration->getId(),
            );
        }
    }

}