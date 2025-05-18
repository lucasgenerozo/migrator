<?php

use LucasGenerozo\Migrator\Models\Domain\Treatment;
use PHPUnit\Framework\TestCase;

class TreatmentTest extends TestCase
{
    public static function providerNumberExamples(): array
    {
        $treatment = new Treatment(
            null,
            'multiplier',
            '$input, $multiplier',
            'return $input * $multiplier;'
        );

        return [
            [$treatment, 1, 3, 3],
            [$treatment, 999999, 2, 1999998],
            [$treatment, 57, 4, 228],
            [$treatment, 5.5, 1, 5.5],
        ];
    }

    /** @dataProvider providerNumberExamples */
    public function testTreatmentExemploDeveFuncionar(
        Treatment $treatment, 
        float $input, 
        int $multiplier, 
        float $expected
    ): void {
        $actual = $treatment($input, $multiplier);

        self::assertEquals(
            $expected,
            $actual
        );
    }

    public function testTreatmentComFunctionStringInvalidaDeveLancarException(): void
    {
        $this->expectException(ParseError::class);

        $treatment = new Treatment(
            null,
            'multiplier',
            '$input, $multiplier',
            'retur input * multiplier'
        );
    }

    public function testTreatmentComParametersStringVazioDeveLancarException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $treatment = new Treatment(
            null,
            'multiplier',
            '',
            'return input * multiplier'
        );
    }
}