<?php

use Lucas\Tcc\Models\Infrastructure\PDO\DataSource\PDODataSource;
use PHPUnit\Framework\TestCase;

class PDODataSourceTest extends TestCase
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
        return [[new PDODataSource(
            'users',
            self::sqlitePdoCreator(),
        )]];
    }

    /** @dataProvider usersPdoDataSource */
    public function testListAll(PDODataSource $dataSource)
    {
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
        ];

        self::assertEqualsCanonicalizing(
            $expected,
            $dataSource->listAll(),
        );
    }

    /** @dataProvider usersPdoDataSource */
    public function testListByCallback(PDODataSource $dataSource)
    {
        $expected = [
            [
                'id' => 2,
                'name' => 'bar',
            ],
            [
                'id' => 3,
                'name' => 'php',
            ],
        ];

        self::assertEqualsCanonicalizing(
            $expected,
            $dataSource->listByCallback(fn ($inp) => $inp['id'] > 1),
        );
    }

    /** @dataProvider usersPdoDataSource */
    public function testListBy(PDODataSource $dataSource)
    {
        $expected = [
            [
                'id' => 1,
                'name' => 'foo',
            ],
            [
                'id' => 2,
                'name' => 'bar',
            ],
        ];

        self::assertEqualsCanonicalizing(
            $expected,
            $dataSource->listBy([
                ['id', '<', '3'],
            ]),
        );
    }

    /** @dataProvider usersPdoDataSource */
    public function testFirstBy(PDODataSource $dataSource)
    {
        $expected = [
            'id' => 1,
            'name' => 'foo',
        ];

        self::assertEqualsCanonicalizing(
            $expected,
            $dataSource->firstBy([
                ['id', '<', '3'],
            ]),
        );
    }
}