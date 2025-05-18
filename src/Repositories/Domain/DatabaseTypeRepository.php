<?php
namespace LucasGenerozo\Migrator\Repositories\Domain;

use LucasGenerozo\Migrator\Models\Domain\Database\DatabaseType;

interface DatabaseTypeRepository
{
    public function list(): ?array;
    public function find(int $id): DatabaseType;
}