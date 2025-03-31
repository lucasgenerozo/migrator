<?php
namespace Lucas\Tcc\Models\Domain;

use Lucas\Tcc\Repositories\Domain\DatabaseRepository;

class Collection
{
    public function __construct(
        private ?int $id,
        private Database $origin,
        private Database $destiny,
        private array $migrations,
    )
    {
    }

    public function getOriginDatabase(): Database
    {
        return $this->origin;
    }

    public function getDestinyDatabase(): Database
    {
        return $this->origin;
    }

    public function getMigrations(): array
    {
        return $this->migrations;
    }

    private function prepareMigrations()
    {
        $migrations = $this->getMigrations();

        foreach($migrations as $migration) {

            $from = $this->getOriginDatabase()->getDataSource($migration->from, $migration->fromWith);
            $to = $this->getDestinyDatabase()->getDataSource($migration->to, $migration->toWith);

            $this->migrations[] = new Migration(
                $from,
                $to,
                $migration->connections
            );
        }
    }
}