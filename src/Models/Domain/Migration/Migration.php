<?php
namespace Lucas\Tcc\Models\Domain\Migration;

use InvalidArgumentException;
use Lucas\Tcc\Models\Domain\DataSource\DataSource;
use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;
use Lucas\Tcc\Models\Domain\Entity;
use Lucas\Tcc\Repositories\Domain\TreatmentRepository;

class Migration extends Entity
{
    private array $insert_columns;
    private array $treatments;
    private array $treatment_columns;
    
    private array $connections;

    public function __construct(
        ?int $id,
        private DataSource $from,
        private WritableDataSource $to,
        array $connections,
        private TreatmentRepository $treatmentRepository,
        private MigrationStatus $status,
        private ?array $fromClauses = null,
    )
    {
        $this->setId($id);
        $this->setConnections($connections);
        $this->prepare();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setConnections(array $connections): void
    {
        if (empty($connections)) {
            throw new InvalidArgumentException('Connections cant be null');
        }
        
        $this->connections = $connections;
    }
    
    protected function prepareFunctions(): void
    {
        if (empty($this->connections)) {
            throw new InvalidArgumentException('Connections must be defined before prepareFunctions');
        }

        $insert_columns = array();
        $treatments = array();
        $treatment_columns = array();

        foreach ($this->connections as $connection) {
            
            $connection_to = $connection['to'];
            $connection_from = $connection['from'];

            // recebe todas as colunas que vão ser inseridas na chave, e no valor a coluna
            $insert_columns[$connection_from] = $connection_to;

            if (!is_null($connection['treatment'])) {
                $treatment_id = $connection['treatment'];

                $treatment_columns[$connection_from] = $treatment_id;
                $treatments[$treatment_id] = $this->treatmentRepository->find($treatment_id);
            }
        }

        $this->insert_columns = $insert_columns;
        $this->treatments = $treatments;
        $this->treatment_columns = $treatment_columns;
    }

    public function prepare(): Migration
    {
        $this->prepareFunctions();

        return $this;
    }

    protected function dataToInsertModel(array $data): array
    {
        $insert_model = array();

        foreach ($data as $from_column => $value) {
            if (!array_key_exists($from_column, $this->insert_columns)) {
                continue;
            }

            $to_column = $this->insert_columns[$from_column];
            
            $insert_value = $value;
            if (array_key_exists($from_column, $this->treatment_columns)) {
                $treatment_id = $this->treatment_columns[$from_column];
                $treatment = $this->treatments[$treatment_id];

                $insert_value = $treatment($value);
            }

            $insert_model[$to_column] = $insert_value;
        }

        return $insert_model;
    }

    protected function getDataList(): ?array
    {
        if (!empty($this->fromClauses)) {
            return $this->from->listBy($this->fromClauses);
        } 

        return $this->from->listAll();
    }

    public function execute(): void
    {
        $this->status = MigrationStatus::Executing;

        foreach ($this->getDataList() as $data) {
            $insert_model = $this->dataToInsertModel($data);

            $this->to->add($insert_model);
        }

        $this->status = MigrationStatus::Complete;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'from' => $this->from->getName(),
            'to' => $this->from->getName(),
            'connections' => $this->connections,
            'status' => $this->status->value,
        ];
    }

    public static function fromArray(array $data): mixed
    {
        return new Migration(
            $data['id'],
            $data['from'],
            $data['to'],
            $data['connections'],
            $data['treatmentRepository'],
            $data['status'],
            $data['fromClauses'],
        );
    }
}