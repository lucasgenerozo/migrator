<?php
namespace Lucas\Tcc\Models\Domain;

use Lucas\Tcc\Models\Domain\DataSource\DataSource;

interface Database
{
    public function __construct(
        ?int $id,
        string $driver, 
        string $name,
        array $config,
    );

    public function getDataSource(string $name, ?array $with): ?DataSource;
}