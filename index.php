<?php

interface InterfaceDataSource
{
    public function __construct(
        DataBase &$database,
        string $name,
        ?array $with,
    );
}

class DataBase
{
    public function __construct(
        private $driver,
        private $repository
    )
    {
        
    }

    public function getDataSource(string $name, ?array $with): ?InterfaceDataSource
    {
        return new InterfaceDataSource(
            $this,
            $name,
            $with
        );
    }
}

abstract class DataSource implements InterfaceDataSource
{
    public function __construct(
        private DataBase &$database,
        private string $name,
        private ?array $with,
    )
    {}
}

class Migration
{
    public function __construct(
        private DataSource $from,
        private DataSource $to,
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

class Loader
{
    private array $migrations;

    private $originDataBase;
    private $destinyDataBase;

    public function loadCollection($collection)
    {

    }

    public function loadMigrations(array $migrations)
    {
        foreach($migrations as $migration) {

            $from = $this->originDataBase->getDataSource($migration->from, $migration->fromWith);
            $to = $this->originDataBase->getDataSource($migration->to, $migration->toWith);

            $this->migrations[] = new Migration(
                $from,
                $to,
                $migration->connections
            );
        }
    }

    public function execute()
    {
        foreach($this->migrations as $migration) {
            $migration->prepare()
                      ->execute();

        }
    }
}