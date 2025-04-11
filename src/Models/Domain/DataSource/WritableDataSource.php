<?php
namespace Lucas\Tcc\Models\Domain\DataSource;

interface WritableDataSource extends DataSource
{
    public function add(mixed $data): bool;
}