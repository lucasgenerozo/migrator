<?php
namespace Lucas\Tcc\Repositories\Domain;

use Lucas\Tcc\Models\Domain\Collection;

interface CollectionRepository
{
    public function list(): ?array;
    public function find(int $id): ?Collection;
    public function save(Collection $database): void;
    public function remove(Collection $database): void;
}