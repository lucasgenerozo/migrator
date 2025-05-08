<?php
namespace Lucas\Tcc\Models\Domain\Database;

use Lucas\Tcc\Models\Domain\Database\DatabaseType;
use Lucas\Tcc\Models\Domain\DataSource\DataSource;

interface Database
{
    public function __construct(
        ?int $id,
        DatabaseType $type, 
        string $name,
        array $options,
    );

    public function getId(): ?int;

    public function getDataSource(string $name, ?array $with = []): ?DataSource;
}