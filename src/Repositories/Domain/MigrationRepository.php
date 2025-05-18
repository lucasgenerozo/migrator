<?php
namespace LucasGenerozo\Migrator\Repositories\Domain;

use LucasGenerozo\Migrator\Models\Domain\Collection;

interface MigrationRepository
{
    public function listByCollection(Collection $collection): ?array;
}