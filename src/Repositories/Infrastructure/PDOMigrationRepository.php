<?php
namespace LucasGenerozo\Migrator\Repositories\Infrastructure;

use LucasGenerozo\Migrator\Exceptions\ResourceNotFound;
use LucasGenerozo\Migrator\Models\Domain\Collection;
use LucasGenerozo\Migrator\Models\Domain\DataSource\WritableDataSource;
use LucasGenerozo\Migrator\Models\Domain\Migration\Migration;
use LucasGenerozo\Migrator\Models\Domain\Migration\MigrationStatus;
use LucasGenerozo\Migrator\Models\Infrastructure\PDO\DataSource\PDOWritableDataSource;
use LucasGenerozo\Migrator\Repositories\Domain\MigrationRepository;
use LucasGenerozo\Migrator\Repositories\Domain\TreatmentRepository;
use PDO;

class PDOMigrationRepository implements MigrationRepository
{
    private WritableDataSource $dataSource;
    
    public function __construct(
        private PDO $pdo,
        private TreatmentRepository $treatmentRepository,
    )
    {
        $this->dataSource = new PDOWritableDataSource(
            'migrations',
            $pdo,
        );
    }
    
    public function hydrateMigration(array $data, Collection $collection): Migration
    {
        $json = json_decode($data['json'], associative: true, flags: JSON_THROW_ON_ERROR);

        $data['from'] = $collection->getOriginDatabase()->getDataSource($json['from']['identifier'], $json['from']['with']);
        $data['to'] = $collection->getDestinyDatabase()->getDataSource($json['to']['identifier'], $json['to']['with']);
        $data['connections'] = $json['connections'];
        $data['status'] = MigrationStatus::from($data['status']);

        return new Migration(
            $data['id'],
            $data['from'],
            $data['to'],
            $data['connections'],
            $this->treatmentRepository,
            $data['status'],
        );
    }

    /**
     * @return ?Migration[]
     */
    public function listByCollection(Collection $collection): ?array
    {
        $collection_id = $collection->getId();

        if (is_null($collection_id)) {
            return [];
        }

        $migrationDataList = $this->dataSource->listBy([
            ['collection_id', '=', $collection_id],
        ]);
        $migrationList = [];

        foreach ($migrationDataList as $migrationData) {
            $migrationList[] = $this->hydrateMigration($migrationData, $collection);
        }
        
        return $migrationList;
    }

    private function insert(array &$migrationData): void
    {
        $migrationData['id'] = $this->dataSource->add($migrationData);
    }

    private function update(array $migrationData): void
    {
        $this->dataSource->edit(
            [
                ['id', '=', $migrationData['id']]
            ],
            $migrationData,
        );
    }

    public function save(array &$migrationData): void
    {
        $function_name = is_null($migrationData['id']) ? 'insert' : 'update';

        $this->$function_name($migrationData);

    }

    public function remove(int $id): void
    {
        $this->dataSource->remove([
            ['id', '=', $id],
        ]);
    }
}