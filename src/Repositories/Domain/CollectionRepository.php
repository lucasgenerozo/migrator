<?php
namespace Lucas\Tcc\Repositories\Domain;

use Lucas\Tcc\Models\Domain\Collection;

interface CollectionRepository
{
    public function list(): ?array;
    public function findWithMigration(int $id): Collection;
    public function find(int $id): Collection;
    public function save(Collection &$collection): void;
    public function remove(int $id): void;
}