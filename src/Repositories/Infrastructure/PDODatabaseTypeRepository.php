<?php
namespace Lucas\Tcc\Repositories\Infrastructure;

use Lucas\Tcc\Exceptions\ResourceNotFound;
use Lucas\Tcc\Models\Domain\Database\DatabaseType;
use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;
use Lucas\Tcc\Models\Infrastructure\PDO\DataSource\PDOWritableDataSource;
use Lucas\Tcc\Repositories\Domain\DatabaseTypeRepository;
use PDO;

class PDODatabaseTypeRepository implements DatabaseTypeRepository
{
    private WritableDataSource $dataSource;

    public function __construct(private PDO $pdo)
    {
        $this->dataSource = new PDOWritableDataSource(
            'database_types',
            $pdo,
        );
    }

    public static function hydrateDatabaseType(array $data): DatabaseType
    {
        return new DatabaseType(
            $data['id'],
            $data['name'],
            ($data['writable'] == 1),
        );
    }

    public function list(): ?array
    {
        $typeDataList = $this->dataSource->listAll();
        $typeList = [];

        foreach ($typeDataList as $typeData) {
            $typeList[] = self::hydrateDatabaseType($typeData);
        }

        return $typeList;
    }

    public function find(int $id): DatabaseType
    {
        $typeData = $this->dataSource->firstBy([
            ['id', '=', $id],
        ]);

        if (empty($typeData)) {
            throw new ResourceNotFound(
                DatabaseType::class,
                ['id', '=', $id],
            );
        }

        return self::hydrateDatabaseType($typeData);
    }
}