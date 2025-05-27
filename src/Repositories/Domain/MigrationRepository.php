<?php
namespace LucasGenerozo\Migrator\Repositories\Domain;

use LucasGenerozo\Migrator\Models\Domain\Collection;

interface MigrationRepository
{
    public function listByCollection(Collection $collection): ?array;
    public function save(array &$migrationData): void;
    public function remove(int $id): void;
}