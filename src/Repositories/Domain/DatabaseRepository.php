<?php
namespace LucasGenerozo\Migrator\Repositories\Domain;

use LucasGenerozo\Migrator\Models\Domain\Database\Database;

interface DatabaseRepository
{
    public function find(int $id): Database;
}