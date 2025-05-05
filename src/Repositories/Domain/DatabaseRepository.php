<?php
namespace Lucas\Tcc\Repositories\Domain;

use Lucas\Tcc\Models\Domain\Database\Database;

interface DatabaseRepository
{
    public function find(int $id): Database;
}