<?php

use Lucas\Tcc\Models\Domain\Database\DatabaseType;
use Lucas\Tcc\Repositories\Infrastructure\PDODatabaseTypeRepository;
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

    public function testRepositoryDeveListarCorretamente(): void
    {
        $repository = new PDODatabaseTypeRepository(self::sqlitePDO());

        $typeList = $repository->list();

        $expected = [
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

        self::assertEqualsCanonicalizing(
            $expected,
            $typeList,
        );
    }
}
