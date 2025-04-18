<?php
namespace Lucas\Tcc\Models\Domain;

use Lucas\Tcc\Models\Domain\DataSource\DataSource;
use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;
use Lucas\Tcc\Repositories\Domain\TreatmentRepository;

class Migration
{
    public function __construct(
        private DataSource $from,
        private WritableDataSource $to,
        private array $connections,
        private TreatmentRepository $treatmentRepository,
    )
    {
        $insert_columns = array();
        $treatments = array();
        $treatment_columns = array();

        foreach ($connections as $connection) {
            
            if (!is_null($connection['treatment'])) {
                $treatment_id = $connection['treatment'];
                $connection_to = $connection['to'];

                $treatments[$treatment_id] = $treatmentRepository->find($treatment_id);
                $insert_columns[] = $connection_to;
                $treatment_columns[$connection_to] = $treatment_id;
            }
        }
    }

    public function prepareFunctions(): void
    {

    }

    public function prepare(): Migration
    {

        # monta a ou as sqls aqui e guarda

        return $this;
    }

    public function execute(): bool
    {
        # executa a inserção, por exemplo
    }
}