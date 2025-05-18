<?php
namespace LucasGenerozo\Migrator\Models\Domain;

interface ArraySerializable
{
    public function toArray(): array;
    public static function fromArray(array $data): mixed;
}