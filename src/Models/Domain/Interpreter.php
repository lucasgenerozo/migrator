<?php
namespace LucasGenerozo\Migrator\Models\Domain;

use DI\Container;
use Exception;
use LucasGenerozo\Migrator\Repositories\Domain\CollectionRepository;

class Interpreter
{

    private CollectionRepository $collectionRepository;

    private Collection $collection;
    private array $migrations;

    public function __construct(
        private Container $container,
        int $id_collection
    )
    {
        $this->collectionRepository = $container->get(CollectionRepository::class);
        
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
        $this->collection = $this->collectionRepository->findWithMigration($id);
    }

    public function execute()
    {
        foreach ($this->collection->getMigrations() as $migration) {
            $migration->execute();
        }
    }
}