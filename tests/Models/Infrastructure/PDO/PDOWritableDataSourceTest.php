<?php

use LucasGenerozo\Migrator\Models\Infrastructure\PDO\DataSource\PDOWritableDataSource;
use PHPUnit\Framework\TestCase;

class PDOWritableDataSourceTest extends TestCase
{
    public static function sqlitePdoCreator(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT
            );
        ");
        $pdo->query("
            INSERT INTO users (id, name) VALUES (1, 'foo'), (2, 'bar'), (3, 'php'); 
        ");
        
        return $pdo;
    }

    public static function usersPdoDataSource(): array
    {
        return [[new PDOWritableDataSource(
            'users',
            self::sqlitePdoCreator(),
        )]];
    }

    /** @dataProvider usersPdoDataSource */
    public function testAdd(PDOWritableDataSource $dataSource)
    {
        $dataSource->add([
            'name' => 'pdo',
        ]);

        $expected = [
            [
                'id' => 1,
                'name' => 'foo',
            ],
            [
                'id' => 2,
                'name' => 'bar',
            ],
            [
                'id' => 3,
                'name' => 'php',
            ],
            [
                'id' => 4,
                'name' => 'pdo',
            ],
        ];

        self::assertEqualsCanonicalizing(
            $expected,
            $dataSource->listAll(),
        );
    }

    /** @dataProvider usersPdoDataSource */
    public function testEdit(PDOWritableDataSource $dataSource): void
    {
        $dataSource->edit(
            [
                ['id', '=', 2],
            ],
            [
                'name' => 'pdo',
            ],
        );

        $expected = [
            [
                'id' => 1,
                'name' => 'foo',
            ],
            [
                'id' => 2,
                'name' => 'pdo',
            ],
            [
                'id' => 3,
                'name' => 'php',
            ],
        ];

        self::assertEqualsCanonicalizing(
            $expected,
            $dataSource->listAll(),
        );
    }

    /** @dataProvider usersPdoDataSource */
    public function testRemove(PDOWritableDataSource $dataSource): void
    {
        $dataSource->remove(
            [
                ['id', '>=', 2],
            ]
        );

        $expected = [
            [
                'id' => 1,
                'name' => 'foo',
            ],
        ];

        self::assertEqualsCanonicalizing(
            $expected,
            $dataSource->listAll(),
        );
    }
}