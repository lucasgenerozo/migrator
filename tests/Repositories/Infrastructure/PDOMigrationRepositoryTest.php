<?php

use Lucas\Tcc\Repositories\Domain\TreatmentRepository;
use Lucas\Tcc\Repositories\Infrastructure\PDOTreatmentRepository;
use PHPUnit\Framework\TestCase;

class PDOMigrationRepositoryTest extends TestCase
{
    public static function emptySqlitePDOCreator(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    }

    public function treatmentRepositoryCreator(): TreatmentRepository
    {
        return new PDOTreatmentRepository(self::emptySqlitePDOCreator());
    }

    

}