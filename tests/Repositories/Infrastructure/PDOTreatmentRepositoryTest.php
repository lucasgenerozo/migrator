<?php

use LucasGenerozo\Migrator\Models\Domain\Treatment;
use LucasGenerozo\Migrator\Repositories\Infrastructure\PDOTreatmentRepository;
use PHPUnit\Framework\TestCase;

class PDOTreatmentRepositoryTest extends TestCase
{
    private static function sqlitePDO(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('
            CREATE TABLE treatments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                parameters TEXT,
                function TEXT
            );
        ');
        $pdo->query('
            INSERT INTO treatments 
            (id, name, parameters, function) 
            VALUES 
            (1, "multiplier", "$input, $multiplier", "return $input * $multiplier;"),
            (2, "toUpper", "$input", "return strtoupper($input);");
        ');

        return $pdo;
    }

    public static function providerTreatmentRepository(): array
    {
        return [
            [new PDOTreatmentRepository(self::sqlitePDO())],
        ];
    }

    /** @dataProvider providerTreatmentRepository */
    public function testRepositoryDeveListarCorretamente(PDOTreatmentRepository $repository): void
    {
        $treatmentList = $repository->list();
        $expected = [
            new Treatment(
                1,
                'multiplier',
                '$input, $multiplier',
                'return $input * $multiplier;'
            ),
            new Treatment(
                2,
                'toUpper',
                '$input',
                'return strtoupper($input);'
            ),
        ];

        self::assertEqualsCanonicalizing(
            $expected,
            $treatmentList,
        );

        self::assertCount(
            2,
            $treatmentList,
        );
    }

    /** @dataProvider providerTreatmentRepository */
    public function testRepositoryDeveInserirRegistroCorretamenteEAtualizarId(PDOTreatmentRepository $repository): void
    {
        $treatment = new Treatment(
            null,
            'contaStr',
            '$input',
            'return strlen($input);',
        );

        $repository->save($treatment);

        $treatmentList = $repository->list();
        $expected = [
            new Treatment(
                1,
                'multiplier',
                '$input, $multiplier',
                'return $input * $multiplier;'
            ),
            new Treatment(
                2,
                'toUpper',
                '$input',
                'return strtoupper($input);'
            ),
            $treatment
        ];

        self::assertEqualsCanonicalizing(
            $expected,
            $treatmentList,
        );

        self::assertCount(
            3,
            $treatmentList,
        );

        self::assertEquals(
            3,
            $treatment->getId(),
        );
    }

    /** @dataProvider providerTreatmentRepository */
    public function testRepositoryDeveAtualizarRegistroCorretamenteENaoAtualizarId(PDOTreatmentRepository $repository): void
    {
        $treatment = new Treatment(
            2,
            'contaStr',
            '$input',
            'return strlen($input);',
        );

        $repository->save($treatment);

        $treatmentList = $repository->list();
        $expected = [
            new Treatment(
                1,
                'multiplier',
                '$input, $multiplier',
                'return $input * $multiplier;'
            ),
            $treatment,
        ];

        self::assertEqualsCanonicalizing(
            $expected,
            $treatmentList,
        );

        self::assertCount(
            2,
            $treatmentList,
        );

        self::assertEquals(
            2,
            $treatment->getId(),
        );
    }

    /** @dataProvider providerTreatmentRepository */
    public function testRepositoryDeveRemoverRegistroCorretamente(PDOTreatmentRepository $repository): void
    {
        $repository->remove(1);

        $treatmentList = $repository->list();
        $expected = [
            new Treatment(
                2,
                'toUpper',
                '$input',
                'return strtoupper($input);'
            ),
        ];

        self::assertEqualsCanonicalizing(
            $expected,
            $treatmentList,
        );

        self::assertCount(
            1,
            $treatmentList,
        );
    }

    /** @dataProvider providerTreatmentRepository */
    public function testRepositoryDeveEncontrarRegistroCorretamente(PDOTreatmentRepository $repository): void
    {
        $treatment = $repository->find(2);
        $treatmentExpected = new Treatment(
            2,
            'toUpper',
            '$input',
            'return strtoupper($input);'
        );

        self::assertEquals(
            $treatment->getId(),
            $treatmentExpected->getId(),
        );
    }
}