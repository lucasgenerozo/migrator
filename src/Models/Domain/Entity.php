<?php
namespace LucasGenerozo\Migrator\Models\Domain;

abstract class Entity implements ArraySerializable
{
    protected ?int $id = null;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}