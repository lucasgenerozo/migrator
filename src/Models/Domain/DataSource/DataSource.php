<?php
namespace Lucas\Tcc\Models\Domain\DataSource;

interface DataSource
{
    public function __construct(
        string $name,
        ?array $with,
    );

    public function listAll(): ?array;
    public function listByCallback(callable $callback): ?array;
    public function findBy(string $label, mixed $value): mixed;
}