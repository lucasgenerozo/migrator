<?php
namespace Lucas\Tcc\Models\Domain\DataSource;

interface DataSource
{
    public function __construct(
        string $name,
        ?array $with,
    );
}