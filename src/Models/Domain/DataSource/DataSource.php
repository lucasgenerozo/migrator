<?php
namespace LucasGenerozo\Migrator\Models\Domain\DataSource;

interface DataSource
{
    public function __construct(
        string $name,
        mixed $connection,
        ?array $with = null,
        ?array $additional = null
    );

    public function getName(): string;

    public function listAll(): ?array;
    public function listByCallback(callable $callback): ?array;
    public function listBy(array $searches, int $limit = 0): ?array;
    public function firstBy(array $searches): ?array;
}