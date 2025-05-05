<?php

use Lucas\Tcc\Exceptions\ResourceNotFound;
use Lucas\Tcc\Models\Domain\Collection;
use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;
use Lucas\Tcc\Repositories\Domain\CollectionRepository;
use Lucas\Tcc\Repositories\Domain\MigrationRepository;

class PDOCollectionRepository implements CollectionRepository
{
    private WritableDataSource $dataSource;

    public function __construct(
        private PDO $pdo,
        private MigrationRepository $migrationRepository,
    ) {
        $this->dataSource = new WritableDataSource(
            'collections',
            $pdo
        );
    }

    public static function hydrateCollection(array $data): Collection
    {
        return new Collection(
            $data['id'],
            $data['origin'],
            $data['destiny'],
            $data['migrations'],
        );
    }

    public function find(int $id): ?Collection
    {
        $data = $this->dataSource->listBy([
            ['id', '=', $id],
        ]);

        if (empty($data)) {
            throw new ResourceNotFound(
                Collection::class,
                ['id', '=', $id],
            );
        }

        return self::hydrateCollection($data);
    }

    public function list(): ?array
    {
        $collectionDataList = $this->dataSource->listAll();
        $collectionList = [];

        foreach ($collectionDataList as $collectionData) {
            $collectionList = self::hydrateCollection($collectionData);
        }

        return $collectionList;
    }

    private function insert(Collection &$collection): void
    {
        $collection->setId(
        $this->dataSource->add(
            $collection->toArray()
            )
        );
    }

    private function update(Collection &$collection): void
    {
        $this->dataSource->edit(
            [
                ['id', '=', $collection->getId()]
            ],
            $collection->toArray()
        );
    }

    public function save(Collection &$collection): void
    {
        $function_name = is_null($collection->getId()) ? 'insert' : 'update';

        $this->$function_name($collection);
    }

    public function remove(int $id): void
    {
        $this->dataSource->remove([
            ['id', '=', $id],
        ]);
    }

    public function listWithMigrations(): ?array
    {
        $collectionDataList = $this->dataSource->listAll();
        $collectionList = [];

        foreach ($collectionDataList as $collectionData) {
            $collectionData['origin'] = ; // pegar do DatabaseRepository;
            $collectionData['destiny'] = ; // pegar do DatabaseRepository;

            $collection    = self::hydrateCollection($collectionData);
            $migrationList = $this->migrationRepository->listByCollection($collection);

            $collection->setMigrations($migrationList);
        }
    }
}