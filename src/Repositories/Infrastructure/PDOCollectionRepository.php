<?php
namespace LucasGenerozo\Migrator\Repositories\Infrastructure;

use LucasGenerozo\Migrator\Exceptions\ResourceNotFound;
use LucasGenerozo\Migrator\Models\Domain\Collection;
use LucasGenerozo\Migrator\Models\Domain\DataSource\WritableDataSource;
use LucasGenerozo\Migrator\Models\Infrastructure\PDO\DataSource\PDOWritableDataSource;
use LucasGenerozo\Migrator\Repositories\Domain\CollectionRepository;
use LucasGenerozo\Migrator\Repositories\Domain\DatabaseRepository;
use LucasGenerozo\Migrator\Repositories\Domain\MigrationRepository;
use PDO;

class PDOCollectionRepository implements CollectionRepository
{
    private WritableDataSource $dataSource;

    public function __construct(
        private PDO $pdo,
        private DatabaseRepository $databaseRepository,
        private MigrationRepository $migrationRepository,
    ) {
        $this->dataSource = new PDOWritableDataSource(
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
            $data['migrations'] ?? null,
        );
    }

    public function find(int $id): Collection
    {
        $data = $this->dataSource->firstBy([
            ['id', '=', $id],
        ]);

        if (empty($data)) {
            throw new ResourceNotFound(
                Collection::class,
                ['id', '=', $id],
            );
        }

        $data['origin'] = $this->databaseRepository->find($data['database_origin']);
        $data['destiny'] = $this->databaseRepository->find($data['database_destiny']);

        return self::hydrateCollection($data);
    }

    public function findWithMigration(int $id): Collection
    {
        $collection = $this->find($id);
        $migrations = $this->migrationRepository->listByCollection($collection);
        $collection->setMigrations($migrations);

        return $collection;
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
}