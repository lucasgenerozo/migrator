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

    public static function hydrateDatabase(array $data): Database
    {
        return new PDODatabase(
            $data['id'],
            $data['type'],
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

        $databaseData['type'] = $this->databaseTypeRepository->find($databaseData['type_id']);
        $databaseData['config'] = json_decode($databaseData['config'], associative: true, flags: JSON_THROW_ON_ERROR);

        return self::hydrateDatabase($databaseData);
    }
}