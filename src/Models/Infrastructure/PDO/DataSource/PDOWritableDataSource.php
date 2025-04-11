<?php
namespace Lucas\Tcc\Models\Infrastructure\PDO\DataSource;

use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;

class PDOWritableDataSource extends PDODataSource implements WritableDataSource
{
    public function add(mixed $data): bool
    {
        $column_names = implode(',', array_keys($data));
        $values_holder = implode(', ', array_fill(0, count($data), '?'));

        $this->pdo->prepare("
            INSERT INTO $this->name ($column_names) VALUES ($values_holder);
        ");

        // TODO: 
    }
}