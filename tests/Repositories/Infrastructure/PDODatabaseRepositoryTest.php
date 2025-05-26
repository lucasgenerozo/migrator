<?php

use LucasGenerozo\Migrator\Exceptions\ResourceNotFound;
use LucasGenerozo\Migrator\Models\Domain\Database\DatabaseType;
use LucasGenerozo\Migrator\Models\Infrastructure\PDO\PDODatabase;
use LucasGenerozo\Migrator\Repositories\Infrastructure\PDODatabaseRepository;
use LucasGenerozo\Migrator\Repositories\Infrastructure\PDODatabaseTypeRepository;
use PHPUnit\Framework\TestCase;

class PDODatabaseRepositoryTest extends TestCase
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
            (2, 2, 'Master', '{\"dsn\": \"sqlite::memory:\",\"user\":\"\",\"password\":\"\"}'); 
        ");

        return $pdo;
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

    public static function providerDatabaseRepository(): array
    {
        $pdo = self::sqlitePDO();
        $typeRepository = new PDODatabaseTypeRepository($pdo);

        return [
            [
                new PDODatabaseRepository(
                    $pdo,
                    $typeRepository,
                ),
            ],
        ];
    }

    /** @dataProvider providerDatabaseRepository */
    public function testRepositorioDeveEncontrarRegistroCorretamente(PDODatabaseRepository $repository): void
    {
        /** @var PDODatabase */
        $database = $repository->find(2);

        $databaseExpected = new PDODatabase(
            2,
            new DatabaseType(
                2,
                'SQL',
                1,
            ),
            'Master',
            [
                'dsn' => 'sqlite::memory:',
                'user' => '',
                'password' => '',
            ],
        );

        self::assertEquals(
            $database->getId(),
            $databaseExpected->getId(),
        );

        self::assertEquals(
            (self::callPrivateProperty($database, 'type'))->getId(),
            (self::callPrivateProperty($databaseExpected, 'type'))->getId(),
        );

        self::assertEquals(
            $database->getName(),
            $databaseExpected->getName(),
        );
    }

    /** @dataProvider providerDatabaseRepository */
    public function testRepositorioDeveLancarExcecaoCasoNaoEncontreRegistro(PDODatabaseRepository $repository): void
    {
        $this->expectException(ResourceNotFound::class);
        
        $repository->find(3);
    }
    
    /** @dataProvider providerDatabaseRepository */
    public function testRepositorioDeveInserirRegistroCorretamente(PDODatabaseRepository $repository): void
    {
        $newDatabase = new PDODatabase(
            null,
            new DatabaseType(
                2,
                'SQL',
                1,
            ),
            'Master',
            [
                'dsn' => 'sqlite::memory:',
                'user' => '',
                'password' => '',
            ],
        );

        $repository->save($newDatabase);

        self::assertEquals(
            3,
            $newDatabase->getId()
        );

        self::assertCount(
            3,
            $repository->list()
        );

        $database = $repository->find(3);

        self::assertEquals(
            $newDatabase->getId(),
            $database->getId(),
        );

        self::assertEquals(
            self::callPrivateProperty($newDatabase, 'name'),
            self::callPrivateProperty($database, 'name'),
        );
    }

    /** @dataProvider providerDatabaseRepository */
    public function testRepositorioDeveAlterarRegistroCorretamente(PDODatabaseRepository $repository): void
    {
        $newDatabase = new PDODatabase(
            2,
            new DatabaseType(
                1,
                'JSON',
                0,
            ),
            'Client',
            [
                'dsn' => 'sqlite::memory:',
                'user' => '',
                'password' => '',
            ],
        );

        $repository->save($newDatabase);

        self::assertEquals(
            2,
            $newDatabase->getId()
        );

        self::assertCount(
            2,
            $repository->list()
        );

        $database = $repository->find(2);

        self::assertEquals(
            $newDatabase->getId(),
            $database->getId(),
        );

        self::assertEquals(
            self::callPrivateProperty($newDatabase, 'name'),
            self::callPrivateProperty($database, 'name'),
        );

        self::assertEquals(
            'Client',
            self::callPrivateProperty($database, 'name'),
        );
    }

    /** @dataProvider providerDatabaseRepository */
    public function testRepositorioDeveRemoverRegistroCorretamente(PDODatabaseRepository $repository): void
    {
        $repository->remove(2);

        self::assertCount(
            1,
            $repository->list(),
        );
    }
}