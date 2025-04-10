<?php
namespace Lucas\Tcc\Models\Domain;

use DI\Container;
use Exception;
use Lucas\Tcc\Repositories\Domain\CollectionRepository;

class Interpreter
{

    private Collection $collection;

    private CollectionRepository $collectionRepository;

    private array $migrations;

    public function __construct(
        private Container $container,
        string $json,
    )
    {
        $this->collectionRepository = $container->get(CollectionRepository::class);
        
        $this->loadJson($json);
    }

    private function loadJson(string $json): void
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
    private function loadCollection(int $id): void
    {
        $this->collection = $this->collectionRepository->find($id);

        if (is_null($this->collection)) {
            throw new Exception('Collection not found');
        }
    }

    private function prepareMigrations()
    {
        $this->migrations = array();

        foreach($this->collection->getMigrations() as $migration) {

            $from = $this->collection->getOriginDatabase()->getDataSource($migration->from, $migration->fromWith);
            $to = $this->collection->getDestinyDatabase()->getDataSource($migration->to, $migration->toWith);

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