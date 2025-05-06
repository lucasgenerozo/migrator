<?php
namespace Lucas\Tcc\Repositories\Domain;

use Lucas\Tcc\Models\Domain\Database\DatabaseType;

interface DatabaseTypeRepository
{
    public function list(): ?array;
    public function find(int $id): DatabaseType;
}