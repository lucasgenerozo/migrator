<?php

use Lucas\Tcc\Models\Domain\Database\DatabaseType;
use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;
use Lucas\Tcc\Models\Infrastructure\PDO\DataSource\PDODataSource;
use Lucas\Tcc\Models\Infrastructure\PDO\DataSource\PDOWritableDataSource;
use Lucas\Tcc\Models\Infrastructure\PDO\PDODatabase;
use PHPUnit\Framework\TestCase;

class PDODatabaseTest extends TestCase
{
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

    
}