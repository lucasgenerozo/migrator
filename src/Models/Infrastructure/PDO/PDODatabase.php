<?php
namespace Lucas\Tcc\Models\Infrastructure\PDO;

use Lucas\Tcc\Models\Domain\Database;
use Lucas\Tcc\Models\Domain\DataSource\DataSource;
use PDO;

class PDODatabase implements Database
{
    private PDO $connection;

    public function __construct(
        private ?int $id,
        private string $driver, 
        private string $name,
        private array $config,
    )
    {

    }

    public function getDataSource(string $name, ?array $with): ?DataSource
    {
        
    }
}