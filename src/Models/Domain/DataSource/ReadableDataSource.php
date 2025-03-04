<?php
namespace Lucas\Tcc\Models\Domain\DataSource;

interface ReadableDataSource extends DataSource
{
    public function listAll(): ?array;
    public function listWithCallback(callable $callback): ?array;
    public function findBy(string $label, mixed $value): mixed;
}