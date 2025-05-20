<?php
namespace LucasGenerozo\Migrator\Repositories\Domain;

use LucasGenerozo\Migrator\Models\Domain\Database\Database;

interface DatabaseRepository
{
    public function list(): ?array;
    public function find(int $id): Database;
    public function save(Database &$database): void;
    public function remove(int $id): void;
}