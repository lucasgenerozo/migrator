<?php
namespace Lucas\Tcc\Models\Domain;

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
        return $this->origin;
    }

    public function getMigrations(): array
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

            /*$this->migrations[] = new Migration(
                $from,
                $to,
                $migration->connections
            );*/
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
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