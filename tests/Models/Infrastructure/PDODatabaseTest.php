<?php

use LucasGenerozo\Migrator\Models\Domain\Database\DatabaseType;
use LucasGenerozo\Migrator\Models\Domain\DataSource\WritableDataSource;
use LucasGenerozo\Migrator\Models\Infrastructure\PDO\DataSource\PDODataSource;
use LucasGenerozo\Migrator\Models\Infrastructure\PDO\DataSource\PDOWritableDataSource;
use LucasGenerozo\Migrator\Models\Infrastructure\PDO\PDODatabase;
use PHPUnit\Framework\TestCase;

class PDODatabaseTest extends TestCase
{

    public static function sqlitePDO(): PDO
    {
        $pdo = new PDO('sqlite:file::memory:?cache=shared');
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

        return $pdo;
    }

    public static function databaseTypeCreator(bool $writable = false): DatabaseType
    {
        return new DatabaseType(
            1,
            'JSON',
            $writable,
        );
    }
    
    public function testDatabaseNaoPodeSerIniciadaComKeyDsnFaltandoNaConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Required config value missing (dsn)');

        $databaseType = self::databaseTypeCreator();

        new PDODatabase(
            null,
            $databaseType,
            'Master',
            [
                // 'dsn' => '',
                'user' => '',
                'password' => '',
            ]
        );
    }
    
    public function testDatabaseNaoPodeSerIniciadaComKeyUserFaltandoNaConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Required config value missing (user)');

        $databaseType = self::databaseTypeCreator();

        new PDODatabase(
            null,
            $databaseType,
            'Master',
            [
                'dsn' => '',
                // 'user' => '',
                'password' => '',
            ]
        );
    }
    
    public function testDatabaseNaoPodeSerIniciadaComKeyPasswordFaltandoNaConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Required config value missing (password)');

        $databaseType = self::databaseTypeCreator();

        new PDODatabase(
            null,
            $databaseType,
            'Master',
            [
                'dsn' => '',
                'user' => '',
                // 'password' => '',
            ]
        );
    }

    public function testDatabaseComTipoWritableDeveRetornarDataSourceWritable(): void
    {
        $databaseType = self::databaseTypeCreator(true);

        $database = new PDODatabase(
            null,
            $databaseType,
            'Master',
            [
                'dsn' => 'sqlite::memory:',
                'user' => '',
                'password' => '',
            ]
        );
        $dataSource = $database->getDataSource('alunos');
        
        self::assertTrue(
            is_a($dataSource, PDOWritableDataSource::class),
        );
    }

    public function testDatabaseSemTipoWritableDeveRetornarDataSource(): void
    {
        $databaseType = self::databaseTypeCreator();

        $database = new PDODatabase(
            null,
            $databaseType,
            'Master',
            [
                'dsn' => 'sqlite::memory:',
                'user' => '',
                'password' => '',
            ]
        );
        $dataSource = $database->getDataSource('alunos');
        
        self::assertTrue(
            is_a($dataSource, PDODataSource::class),
        );
    }

    public function testDatabaseDeveListarDatasourcesCorretamente(): void
    {
        $pdo = self::sqlitePDO();

        $databaseType = self::databaseTypeCreator();

        $database = new PDODatabase(
            null,
            $databaseType,
            'Master',
            [
                'dsn' => 'sqlite:file::memory:?cache=shared',
                'user' => '',
                'password' => '',
            ]
        );
        $dataSourceList = $database->listDataSources();
        $expectedList = [
            'collections',
            'database_types',
            'databases',
            'treatments',
            'migrations',
        ];

        self::assertEqualsCanonicalizing(
            $expectedList,
            $dataSourceList,
        );
    }

    
}