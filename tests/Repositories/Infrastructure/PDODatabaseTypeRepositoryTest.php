<?php

use LucasGenerozo\Migrator\Exceptions\ResourceNotFound;
use LucasGenerozo\Migrator\Models\Domain\Database\DatabaseType;
use LucasGenerozo\Migrator\Repositories\Infrastructure\PDODatabaseTypeRepository;
use PHPUnit\Framework\TestCase;

class PDODatabaseTypeRepositoryTest extends TestCase
{
    private static function sqlitePDO(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("
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

        return $pdo;
    }

    public static function providerRepository(): array
    {
        return [
            [
                 new PDODatabaseTypeRepository(self::sqlitePDO()),
            ],
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

    public function compareTypes(DatabaseType $type, DatabaseType $typeExpected): void
    {
        self::assertEquals(
            $typeExpected->getId(),
            $type->getId(),
        );

        self::assertEquals(
            self::callPrivateProperty($typeExpected, 'name'),
            self::callPrivateProperty($type, 'name'),
        );
        
        self::assertEquals(
            $typeExpected->isWritable(),
            $type->isWritable(),
        );
    }

    /** @dataProvider providerRepository */
    public function testRepositoryDeveListarCorretamente(PDODatabaseTypeRepository $repository): void
    {
        $typeList = $repository->list();

        $typeExpectedList = [
            new DatabaseType(
                1,
                'JSON',
                false,
            ),
            new DatabaseType(
                2,
                'SQL',
                true,
            ),
            new DatabaseType(
                3,
                'Mongo',
                true,
            ),
        ];

        self::assertCount(
            3,
            $typeList
        );

        foreach ($typeList as $idx => $type) {
            $this->compareTypes($type, $typeExpectedList[$idx]);
        }
    }

    /** @dataProvider providerRepository */
    public function testRepositorioDeveEncontrarRegistroCorretamente(PDODatabaseTypeRepository $repository): void
    {
        $type = $repository->find(1);

        $this->compareTypes(
            $type,
            new DatabaseType(
                1,
                'JSON',
                false,
            ),
        );
    }

    /** @dataProvider providerRepository */
    public function testRepositorioDeveLancarExcecaoCasoNaoEncontreORegistro(PDODatabaseTypeRepository $repository): void
    {
        $this->expectException(ResourceNotFound::class);

        $repository->find(4);
    }
}
