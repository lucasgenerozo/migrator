<?php
namespace LucasGenerozo\Migrator\Repositories\Infrastructure;

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
    
    public function hydrateMigration(array $data): Migration
    {
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

            $json = json_decode($migrationData['json'], associative: true, flags: JSON_THROW_ON_ERROR);

            $migrationData['from'] = $collection->getOriginDatabase()->getDataSource($json['from']['identifier'], $json['from']['with']);
            $migrationData['to'] = $collection->getDestinyDatabase()->getDataSource($json['to']['identifier'], $json['to']['with']);
            $migrationData['connections'] = $json['connections'];
            $migrationData['status'] = MigrationStatus::from($migrationData['status']);

            $migrationList[] = $this->hydrateMigration($migrationData);
        }
        
        return $migrationList;
    }
}