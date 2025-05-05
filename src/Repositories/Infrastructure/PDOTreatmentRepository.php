<?php
namespace Lucas\Tcc\Repositories\Infrastructure;

use Lucas\Tcc\Exceptions\ResourceNotFound;
use Lucas\Tcc\Models\Domain\Treatment;
use Lucas\Tcc\Models\Infrastructure\PDO\DataSource\PDOWritableDataSource;
use Lucas\Tcc\Repositories\Domain\TreatmentRepository;
use PDO;

class PDOTreatmentRepository implements TreatmentRepository
{
    private PDOWritableDataSource $dataSource;

    public function __construct(private PDO $pdo)
    {
        $this->dataSource = new PDOWritableDataSource(
            'treatments',
            $pdo,
        );
    }

    private static function hydrateTreatment(array $data): Treatment
    {
        return new Treatment(
            $data['id'],
            $data['name'],
            $data['parameters'],
            $data['function'],
        );
    }

    public function list(): ?array
    {
        $treatmentDataList = $this->dataSource->listAll();
        $treatmentList = array();

        foreach ($treatmentDataList as $treatmentData) {
            $treatmentList[] = self::hydrateTreatment($treatmentData);
        }

        return $treatmentList;
    }

    public function find(int $id): Treatment
    {
        $treatmentData = $this->dataSource->firstBy([
            ['id', '=', $id]
        ]);
        
        if (is_null($treatmentData)) {
            throw new ResourceNotFound(
                self::class,
                ['id', '=', $id],
            );
        }

        return self::hydrateTreatment($treatmentData);
    }
    
    private function insert(Treatment &$treatment): void
    {
        $treatment->setId(
            $this->dataSource->add(
                $treatment->toArray()
            )
        );
    }

    private function update(Treatment $treatment): void
    {
        $this->dataSource->edit(
            [
                ['id', '=', $treatment->getId()]
            ],
            $treatment->toArray()
        );
    }

    public function save(Treatment &$treatment): void
    {
        $function_name = is_null($treatment->getId()) ? 'insert' : 'update';

        $this->$function_name($treatment);
    }

    public function remove(int $id): void
    {
        $this->dataSource->remove([
            ['id', '=', $id]
        ]);
    }
}