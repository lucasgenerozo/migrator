<?php
namespace Lucas\Tcc\Repositories\Domain;

use Lucas\Tcc\Models\Domain\Collection;

interface MigrationRepository
{
    public function listByCollection(Collection $collection): ?array;
}