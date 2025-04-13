<?php
namespace Lucas\Tcc\Models\Domain;

use Lucas\Tcc\Models\Domain\DataSource\DataSource;
use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;

class Migration
{
    public function __construct(
        private DataSource $from,
        private WritableDataSource $to,
        private array $connections,
    )
    {
        $insert_columns = array();
        $treatments = array();

        foreach ($connections as $connection) {
            $connection_to = $connection['to'];
            
            $insert_columns[] = $connection_to;
            $treatments[$connection_to] = $connection['tratment'];
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