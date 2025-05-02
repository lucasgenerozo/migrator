<?php
namespace Lucas\Tcc\Models\Infrastructure\PDO\DataSource;

use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;
use Lucas\Tcc\Utils\SQLOperator;

class PDOWritableDataSource extends PDODataSource implements WritableDataSource
{
    public function add(array $data): mixed
    {
        $columns = implode(',', array_keys($data));
        $values  = implode(', ', array_fill(0, count($data), '?'));

        $stmt = $this->pdo->prepare("
            INSERT INTO $this->name ($columns) VALUES ($values);
        ");
        $stmt->execute(array_values($data));

        return $this->pdo->lastInsertId();
    }

    public function edit(array $searches, array $data): void
    {
        $sql_operator = new SQLOperator();
        list($clauses_update, ) = $sql_operator->searchesToUpdate($data);
        list($clauses_where, ) = $sql_operator->searchesToWhere($searches);

        $stmt = $this->pdo->prepare("
            UPDATE $this->name
            SET    $clauses_update
            WHERE  $clauses_where
        ");
        $stmt->execute($sql_operator->getAllValues());
    }

    public function remove(array $searches): void
    {
        $sql_operator = new SQLOperator();
        list($clauses, $values) = $sql_operator->searchesToWhere($searches);

        $stmt = $this->pdo->prepare("
            DELETE FROM $this->name
            WHERE  $clauses;
        ");
        $stmt->execute($values);
    }
}