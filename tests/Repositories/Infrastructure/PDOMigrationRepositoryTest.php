<?php

use Lucas\Tcc\Repositories\Domain\TreatmentRepository;
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

    public function treatmentRepositoryCreator(): TreatmentRepository
    {
        return new PDOTreatmentRepository(self::sqlitePDO());
    }

    public function testRepositoryDeveListarMigracoesDeUmaCollectionCorretamente(): void
    {
        // TODO: Implementar
    }

    

}