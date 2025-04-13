<?php
namespace Lucas\Tcc\Models\Infrastructure\PDO;

use Exception;
use Lucas\Tcc\Models\Domain\Database;
use Lucas\Tcc\Models\Domain\DataSource\DataSource;
use Lucas\Tcc\Models\Infrastructure\PDO\DataSource\PDODataSource;
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
        $required_keys = ['dsn', 'user', 'password'];

        foreach (array_keys($config) as $key) {
            if (!in_array($key, $required_keys)) {
                throw new Exception("Required config value missing ($key)");
            }
        } 

        $this->connection = new PDO(
            $config['dsn'],
            $config['user'],
            $config['password'],
        );
    }

    public function getDataSource(string $name, ?array $with = null): ?DataSource
    {
        return new PDODataSource(
            $name,
            $this->connection,
            $with,
        );
    }
}