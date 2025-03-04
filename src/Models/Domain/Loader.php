<?php
namespace Lucas\Tcc\Models\Domain;

use DI\Container;
use Exception;
use Lucas\Tcc\Repositories\Domain\CollectionRepository;

class Loader
{
    private array $migrations;

    private Collection $collection;
    private Database $originDatabase;
    private Database $destinyDatabase;

    private CollectionRepository $collectionRepository;

    public function __construct(
        private Container $container,
        string $json,
    )
    {
        $this->collectionRepository = $container->get(CollectionRepository::class);
        
        $this->loadJson($json);
    }

    public function loadJson(string $json): void
    {
        // aqui tem q definir esses ids
        $id_collection = -1;

        $this->loadCollection($id_collection);
    }

    /**
     * Carrega a coleção da migração de dados
     * @throws Exception
     * @param int $id ID da coleção
     * @return void
     */
    public function loadCollection(int $id): void
    {
        $this->collection = $this->collectionRepository->find($id);

        if (is_null($this->collection)) {
            throw new Exception('Collection not found');
        }
        
        $this->originDatabase = $this->collection->getOriginDatabase();
        $this->destinyDatabase = $this->collection->getOriginDatabase();
    }

    public function loadMigrations(array $migrations)
    {
        foreach($migrations as $migration) {

            $from = $this->originDatabase->getDataSource($migration->from, $migration->fromWith);
            $to = $this->destinyDatabase->getDataSource($migration->to, $migration->toWith);

            $this->migrations[] = new Migration(
                $from,
                $to,
                $migration->connections
            );
        }
    }

    public function execute()
    {
        foreach($this->migrations as $migration) {
            $migration->prepare()
                      ->execute();

        }
    }
}