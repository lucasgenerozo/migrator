<?php
namespace Lucas\Tcc\Models\Domain;

use InvalidArgumentException;
use Lucas\Tcc\Models\Domain\DataSource\DataSource;
use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;
use Lucas\Tcc\Repositories\Domain\TreatmentRepository;

class Migration
{
    private array $insert_columns;
    private array $treatments;
    private array $treatment_columns;
    
    private array $connections;

    public function __construct(
        private DataSource $from,
        private WritableDataSource $to,
        array $connections,
        private TreatmentRepository $treatmentRepository,
    )
    {
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
            
            if (!is_null($connection['treatment'])) {
                $treatment_id = $connection['treatment'];
                $connection_to = $connection['to'];

                $treatments[$treatment_id] = $this->treatmentRepository->find($treatment_id);
                $insert_columns[] = $connection_to;
                $treatment_columns[$connection_to] = $treatment_id;
            }
        }

        $this->insert_columns = $insert_columns;
        $this->treatments = $treatments;
        $this->treatment_columns = $treatment_columns;
    }

    public function prepare(): Migration
    {
        $this->prepareFunctions();


        # monta a ou as sqls aqui e guarda

        return $this;
    }

    public function execute(): bool
    {
        # executa a inserção, por exemplo
    }
}