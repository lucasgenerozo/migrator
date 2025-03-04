<?php
namespace Lucas\Tcc\Repositories\Domain;

use Lucas\Tcc\Models\Domain\Database;

interface DatabaseRepository
{
    public function list(): ?array;
    public function find(int $id): Database;
    public function save(Database $database): void;
    public function remove(Database $database): void;
}