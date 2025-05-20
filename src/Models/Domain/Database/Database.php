<?php
namespace LucasGenerozo\Migrator\Models\Domain\Database;

use LucasGenerozo\Migrator\Models\Domain\Database\DatabaseType;
use LucasGenerozo\Migrator\Models\Domain\DataSource\DataSource;

interface Database
{
    public function __construct(
        ?int $id,
        DatabaseType $type, 
        string $name,
        array $options,
    );

    public function setId(?int $id): void;
    public function getId(): ?int;
    public function getDataSource(string $name, ?array $with = []): ?DataSource;
    public function toArray(): array;
}