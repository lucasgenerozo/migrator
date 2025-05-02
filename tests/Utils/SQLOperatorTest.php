<?php

use Lucas\Tcc\Utils\SQLOperator;
use PHPUnit\Framework\TestCase;

class SQLOperatorTest extends TestCase
{
    public static function providerSQLOperatorVazio(): array
    {
        return [
            [new SQLOperator()],
        ];
    }

    /** @dataProvider providerSQLOperatorVazio */
    public function testWhereClausesDevemSerCriadasCorretamente(SQLOperator $sql_operator): void
    {
        list($clauses, $values) = $sql_operator->searchesToWhere([
            ['id', '>', 3],
            ['deleted', '=', 'N'],
        ]);
        
        self::assertEqualsCanonicalizing(
            "(id > :id0) AND (deleted = :deleted0)",
            $clauses
        );
        self::assertEqualsCanonicalizing(
            [
                ':id0' => 3,
                ':deleted0' => 'N',
            ],
            $values,
        );
    }
    
    /** @dataProvider providerSQLOperatorVazio */
    public function testUpdateClausesDevemSerCriadasCorretamente(SQLOperator $sql_operator): void
    {
        list($clauses, $values)  = $sql_operator->searchesToUpdate([
            'id' => 4,
            'deleted' => 'S'
        ]);

        self::assertEqualsCanonicalizing(
            'id = :id0, deleted = :deleted0',
            $clauses,
        );
        self::assertEqualsCanonicalizing(
            [
                ':id0' => 4,
                ':deleted0' => 'S',
            ],
            $values,
        );
    }

    /** @dataProvider providerSQLOperatorVazio */
    public function testUpdateEWhereClausesDevemSerCriadasCorretamente(SQLOperator $sql_operator): void
    {
        list($clauses_upt, $values_upt)  = $sql_operator->searchesToUpdate([
            'id' => 4,
            'deleted' => 'S'
        ]);

        self::assertEqualsCanonicalizing(
            'id = :id0, deleted = :deleted0',
            $clauses_upt,
        );
        self::assertEqualsCanonicalizing(
            [
                ':id0' => 4,
                ':deleted0' => 'S',
            ],
            $values_upt,
        );

        list($clauses, $values) = $sql_operator->searchesToWhere([
            ['id', '>', 3],
            ['deleted', '=', 'N'],
        ]);
        
        self::assertEqualsCanonicalizing(
            "(id > :id1) AND (deleted = :deleted1)",
            $clauses
        );
        self::assertEqualsCanonicalizing(
            [
                ':id1' => 3,
                ':deleted1' => 'N',
            ],
            $values,
        );
    }
    
}