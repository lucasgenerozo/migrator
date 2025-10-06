<?php
namespace LucasGenerozo\Migrator\Models\Domain;

use LucasGenerozo\Migrator\Models\Domain\Database\Database;
use LucasGenerozo\Migrator\Models\Domain\Migration\Migration;

class Collection extends Entity
{
    public function __construct(
        ?int $id,
        private Database $origin,
        private Database $destiny,
        private ?array $migrations = null,
    )
    {
        $this->setId($id);
    }

    public function getOriginDatabase(): Database
    {
        return $this->origin;
    }

    public function getDestinyDatabase(): Database
    {
        return $this->destiny;
    }

    public function getMigrations(): ?array
    {
        return $this->migrations;
    }

    /**
     * @param Migration[] $migrations
     */
    public function setMigrations(array $migrations): void
    {
        $this->migrations = $migrations;
    }

    private function prepareMigrations()
    {
        $migrations = $this->getMigrations();

        foreach($migrations as $migration) {

            $from = $this->getOriginDatabase()->getDataSource($migration->from, $migration->fromWith);
            $to = $this->getDestinyDatabase()->getDataSource($migration->to, $migration->toWith);
            
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'database_origin' => $this->origin->getId(),
            'database_destiny' => $this->destiny->getId(),
        ];
    }

    public static function fromArray(array $data): mixed
    {
        return new Collection(
            $data['id'],
            $data['origin'],
            $data['destiny'],
            $data['migrations'],
        );
    }

}