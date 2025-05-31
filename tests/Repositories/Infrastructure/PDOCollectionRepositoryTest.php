<?php

use LucasGenerozo\Migrator\Models\Domain\Collection;
use LucasGenerozo\Migrator\Models\Domain\Database\DatabaseType;
use LucasGenerozo\Migrator\Models\Infrastructure\PDO\PDODatabase;
use LucasGenerozo\Migrator\Repositories\Infrastructure\PDOCollectionRepository;
use LucasGenerozo\Migrator\Repositories\Infrastructure\PDODatabaseRepository;
use LucasGenerozo\Migrator\Repositories\Infrastructure\PDODatabaseTypeRepository;
use LucasGenerozo\Migrator\Repositories\Infrastructure\PDOMigrationRepository;
use LucasGenerozo\Migrator\Repositories\Infrastructure\PDOTreatmentRepository;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

class PDOCollectionRepositoryTest extends TestCase
{
    private $databaseRepository;

    public function setUp(): void
    {
        $pdo = self::sqlitePDO();

        $this->databaseRepository = new PDODatabaseRepository(
            $pdo,
            new PDODatabaseTypeRepository($pdo),
        );
    }

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
            (2, 2, 'Origem', '{\"dsn\": \"sqlite::memory:\",\"user\":\"\",\"password\":\"\"}'),
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

    public static function providerCollectionRepository(): array
    {
        $pdo = self::sqlitePDO();

        return [
            [new PDOCollectionRepository(
                $pdo,
                new PDODatabaseRepository(
                    $pdo,
                    new PDODatabaseTypeRepository(
                        $pdo,
                    ),
                ),
                new PDOMigrationRepository(
                    $pdo,
                    new PDOTreatmentRepository(
                        $pdo,
                    ),
                ),
            )]
        ];
    }

    private function compareCollections(Collection $collection, Collection $collectionExpected): void
    {
        self::assertEquals(
            $collection->getId(),
            $collectionExpected->getId(),
        );

        self::assertEquals(
            $collection->getOriginDatabase()->getId(),
            $collectionExpected->getOriginDatabase()->getId(),
        );

        self::assertEquals(
            $collection->getDestinyDatabase()->getId(),
            $collectionExpected->getDestinyDatabase()->getId(),
        );
    }

    private function compareCollectionList(array $collectionList, array $collectionExpectedList): void
    {
        self::assertCount(
            count($collectionExpectedList),
            $collectionList,
        );

        foreach ($collectionExpectedList as $idx => $collectionExpected) {
            
            $collection = $collectionList[$idx];

            $this->compareCollections($collection, $collectionExpected);
        }
    }

    /** @dataProvider providerCollectionRepository */
    public function testCollectionRepositoryDeveEncontrarCollectionCorretamente(PDOCollectionRepository $collectionRepository): void
    {
        $collection = $collectionRepository->find(1);

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

        $collectionExpected = new Collection(
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

        $this->compareCollections($collection, $collectionExpected);
    }

    /** @dataProvider providerCollectionRepository */
    public function testRepositoryDeveListarColecoesCorretamente(PDOCollectionRepository $repository): void
    {
        $collectionList = $repository->list();

        $collection1 = $this->databaseRepository->find(1);
        $collection2 = $this->databaseRepository->find(2);
        $collection3 = $this->databaseRepository->find(3);

        $collectionExpectedList = [
            new Collection(
                1,
                $collection2,
                $collection3,
            ),
            new Collection(
                2,
                $collection1,
                $collection2,
            ),
        ];

        $this->compareCollectionList($collectionList, $collectionExpectedList);
    }
    
    /** @dataProvider providerCollectionRepository */
    public function testRepositoryDeveInserirRegistroCorretamente(PDOCollectionRepository $repository): void
    {
        $collection1 = $this->databaseRepository->find(1);
        $collection2 = $this->databaseRepository->find(2);
        $collection3 = $this->databaseRepository->find(3);

        $newCollection = new Collection(
            null,
            $collection1,
            $collection3,
        );

        $repository->save($newCollection);

        self::assertEquals(
            3,
            $newCollection->getId(),
        );
        
        $collectionList = $repository->list();

        $collectionExpectedList = [
            new Collection(
                1,
                $collection2,
                $collection3,
            ),
            new Collection(
                2,
                $collection1,
                $collection2,
            ),
            new Collection(
                3,
                $collection1,
                $collection3,
            ),
        ];

        $this->compareCollectionList($collectionList, $collectionExpectedList);
    }
    
    /** @dataProvider providerCollectionRepository */
    public function testRepositoryDeveAlterarRegistroCorretamente(PDOCollectionRepository $repository): void
    {
        $collection1 = $this->databaseRepository->find(1);
        $collection2 = $this->databaseRepository->find(2);
        $collection3 = $this->databaseRepository->find(3);

        $newCollection = new Collection(
            2,
            $collection1,
            $collection3,
        );

        $repository->save($newCollection);

        self::assertEquals(
            2,
            $newCollection->getId(),
        );
        
        $collectionList = $repository->list();

        $collectionExpectedList = [
            new Collection(
                1,
                $collection2,
                $collection3,
            ),
            new Collection(
                2,
                $collection1,
                $collection3,
            ),
        ];

        $this->compareCollectionList($collectionList, $collectionExpectedList);
    }

    /** @dataProvider providerCollectionRepository */
    public function testRepositoryDeveRemoverRegistroCorretamente(PDOCollectionRepository $repository): void
    {
        $repository->remove(1);

        $collection1 = $this->databaseRepository->find(1);
        $collection2 = $this->databaseRepository->find(2);
        $collection3 = $this->databaseRepository->find(3);


        $collectionList = $repository->list();

        $collectionExpectedList = [
            new Collection(
                2,
                $collection1,
                $collection2,
            ),
        ];

        $this->compareCollectionList($collectionList, $collectionExpectedList);
    }
}