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