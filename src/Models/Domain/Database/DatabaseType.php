<?php
namespace Lucas\Tcc\Models\Domain\Database;

use Lucas\Tcc\Models\Domain\Entity;

class DatabaseType extends Entity
{
    public function __construct(
        ?int $id,
        private string $name,
        private bool $writable,
    )
    {
        $this->setId($id);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'writable' => $this->writable,
        ];
    }

    public static function fromArray(array $data): DatabaseType
    {
        return new DatabaseType(
            $data['id'],
            $data['name'],
            $data['writable'],
        );
    }
}