<?php
namespace Lucas\Tcc\Repositories\Domain;

use Lucas\Tcc\Models\Domain\Treatment;

interface TreatmentRespository
{
    public function list(): ?array;
    public function find(int $id): Treatment;
    public function save(Treatment $database): void;
    public function remove(Treatment $database): void;
}