<?php
namespace LucasGenerozo\Migrator\Repositories\Domain;

use LucasGenerozo\Migrator\Models\Domain\Treatment;

interface TreatmentRepository
{
    public function list(): ?array;
    public function find(int $id): Treatment;
    public function save(Treatment &$treatment): void;
    public function remove(int $id): void;
}