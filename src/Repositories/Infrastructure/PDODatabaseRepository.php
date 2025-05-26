<?php
namespace LucasGenerozo\Migrator\Repositories\Infrastructure;

use LucasGenerozo\Migrator\Exceptions\ResourceNotFound;
use LucasGenerozo\Migrator\Models\Domain\Database\Database;
use LucasGenerozo\Migrator\Models\Domain\DataSource\WritableDataSource;
use LucasGenerozo\Migrator\Models\Infrastructure\PDO\DataSource\PDOWritableDataSource;
use LucasGenerozo\Migrator\Models\Infrastructure\PDO\PDODatabase;
use LucasGenerozo\Migrator\Repositories\Domain\DatabaseRepository;
use LucasGenerozo\Migrator\Repositories\Domain\DatabaseTypeRepository;
use PDO;

class PDODatabaseRepository implements DatabaseRepository
{
    public WritableDataSource $dataSource;

    public function __construct(
        private PDO $pdo,
        private DatabaseTypeRepository $databaseTypeRepository,
    ) {
        $this->dataSource = new PDOWritableDataSource(
            'databases',
            $pdo
        );
    }

    public function hydrateDatabase(array $data): Database
    {
        $data['type_id'] = $this->databaseTypeRepository->find($data['type_id']);
        $data['config'] = json_decode(
            $data['config'], 
            associative: true, 
            flags: JSON_THROW_ON_ERROR
        );

        return new PDODatabase(
            $data['id'],
            $data['type_id'],
            $data['name'],
            $data['config']
        );
    }

    public function find(int $id): Database
    {
        $databaseData = $this->dataSource->firstBy([
            ['id', '=', $id],
        ]);
        
        if (empty($databaseData)) {
            throw new ResourceNotFound(
                Database::class,
                ['id', '=', $id],
            );
        }


        return $this->hydrateDatabase($databaseData);
    }

    public function list(): ?array
    {
        $databaseDataList = $this->dataSource->listAll();
        $databaseList = [];

        foreach ($databaseDataList as $databaseData) {
            $databaseList[] = $this->hydrateDatabase($databaseData);
        }

        return $databaseList;
    }

    private function insert(Database &$database): void
    {
        $database->setId(
            $this->dataSource->add(
                $database->toArray()
            )
        );
    }

    private function update(Database $database): void
    {
        $this->dataSource->edit(
            [
                ['id', '=', $database->getId()]
            ],
            $database->toArray()
        );
    }

    public function save(Database &$database): void
    {
        $function_name = is_null($database->getId()) ? 'insert' : 'update';

        $this->$function_name($database);
    }

    public function remove(int $id): void
    {
        $this->dataSource->remove([
            ['id', '=', $id]
        ]);
    }
}