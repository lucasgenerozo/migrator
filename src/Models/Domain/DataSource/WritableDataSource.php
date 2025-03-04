<?php
namespace Lucas\Tcc\Models\Domain\DataSource;

interface WritableDataSource extends ReadableDataSource
{
    public function add(mixed $value): bool;
}