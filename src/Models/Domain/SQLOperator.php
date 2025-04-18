<?php
namespace Lucas\Tcc\Models\Domain;

class SQLOperator
{
    private array $existing_clauses = [];
    private array $all_values = [];

    public function searchesToSql(array $searches): array
    {
        $clauses = [];
        $values = [];

        foreach ($searches as $search) {
            list($column, $operator, $value) = $search;

            if (array_key_exists($column, $this->existing_clauses)) {
                $count = ++$this->existing_clauses[$column];
            } else {
                $count = $existing_clauses[$column] = 0;
            }

            $column_alias = "$column$count";

            $clauses[] = "($column $operator :$column_alias)";
            $values[":$column_alias"] = $value;
            $this->all_values[":$column_alias"] = $value;
        }

        return [
            $clauses,
            $values,
        ];
    }

    public function searchesToWhere(array $searches): array
    {
        $result = $this->searchesToSql($searches);
        
        return [
            implode(' AND ', $result[0]),
            $result[1],
        ];
    }

    public function searchesToUpdate(array $searches): array
    {
        $result = $this->searchesToSql($searches);
        
        return [
            implode(',', $result[0]),
            $result[1],
        ];
    }

    public function getAllValues(): array
    {
        return $this->all_values;
    }
}