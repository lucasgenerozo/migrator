<?php
namespace Lucas\Tcc\Repositories\Infrastructure;

use Lucas\Tcc\Exceptions\ResourceNotFound;
use Lucas\Tcc\Models\Domain\Database\Database;
use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;
use Lucas\Tcc\Repositories\Domain\DatabaseRepository;
use PDO;

class PDODatabaseRepository implements DatabaseRepository
{
    public WritableDataSource $dataSource;

    public function __construct(private PDO $pdo)
    {
        $this->dataSource = new WritableDataSource(
            'databases',
            $pdo
        );
    }

    public static function hydrateDatabase(array $data): Database
    {
        return new Database(
            $data['id'],
            $data['driver'],
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

        return self::hydrateDatabase($databaseData);
    }
}