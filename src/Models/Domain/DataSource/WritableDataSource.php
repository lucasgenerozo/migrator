<?php
namespace Lucas\Tcc\Models\Domain\DataSource;

interface WritableDataSource extends DataSource
{
    public function add(array $data): mixed;
    public function edit(array $searches, array $data): void;
    public function remove(array $searches): void;
}