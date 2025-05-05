<?php
namespace Lucas\Tcc\Models\Domain;

use DI\Container;
use Exception;
use Lucas\Tcc\Exceptions\ResourceNotFound;
use Lucas\Tcc\Models\Domain\DataSource\DataSource;
use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;
use Lucas\Tcc\Repositories\Domain\CollectionRepository;
use Lucas\Tcc\Repositories\Domain\TreatmentRepository;

class Interpreter
{

    private CollectionRepository $collectionRepository;
    private TreatmentRepository $treatmentRepository;

    private Collection $collection;
    private array $json;
    private array $migrations;
    private array $connections;

    public function __construct(
        private Container $container,
        int $id_collection
    )
    {
        $this->collectionRepository = $container->get(CollectionRepository::class);
        $this->treatmentRepository = $container->get(TreatmentRepository::class);
        
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
            throw new ResourceNotFound(Collection::class);
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