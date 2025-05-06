<?php
namespace Lucas\Tcc\Repositories\Infrastructure;

use DI\Container;
use Lucas\Tcc\Models\Domain\Collection;
use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;
use Lucas\Tcc\Models\Domain\Migration;
use Lucas\Tcc\Repositories\Domain\MigrationRepository;
use Lucas\Tcc\Repositories\Domain\TreatmentRepository;
use PDO;

class PDOMigrationRepository implements MigrationRepository
{
    private WritableDataSource $dataSource;
    
    public function __construct(
        private PDO $pdo,
        private ?TreatmentRepository $treatmentRepository,
    )
    {
        $this->dataSource = new WritableDataSource(
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
            $this->treatmentRepository
        );
    }

    /**
     * @return ?Migration[]
     */
    public function listByCollection(Collection $collection): ?array
    {
        $migrationDataList = $this->dataSource->listBy([
            ['collection_id', '=', $collection->getId()],
        ]);
        $migrationList = [];

        foreach ($migrationDataList as $migrationData) {

            $json = json_decode($migrationData['json'], flags: JSON_THROW_ON_ERROR);

            $migrationData['from'] = $collection->getOriginDatabase()->getDataSource($json->from->identifier, $json->from->with);
            $migrationData['to'] = $collection->getDestinyDatabase()->getDataSource($json->to->identifier, $json->to->with);
            $migrationData['connections'] = $json->connections;

            $migrationList[] = $this->hydrateMigration($migrationData);
        }
        
        return $migrationList;
    }
}