<?php
namespace Lucas\Tcc\Models\Domain;

use Lucas\Tcc\Models\Domain\DataSource\ReadableDataSource;
use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;

class Migration
{
    public function __construct(
        private ReadableDataSource $from,
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