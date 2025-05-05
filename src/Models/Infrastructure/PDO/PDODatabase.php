<?php
namespace Lucas\Tcc\Models\Infrastructure\PDO;

use InvalidArgumentException;
use Lucas\Tcc\Models\Domain\Database\Database;
use Lucas\Tcc\Models\Domain\Database\DatabaseType;
use Lucas\Tcc\Models\Domain\DataSource\DataSource;
use Lucas\Tcc\Models\Domain\Entity;
use Lucas\Tcc\Models\Infrastructure\PDO\DataSource\PDODataSource;
use PDO;

class PDODatabase extends Entity implements Database
{
    private PDO $connection;

    public function __construct(
        ?int $id,
        private DatabaseType $type, 
        private string $name,
        private array $config,
    )
    {
        $this->setId($id);

        $required_keys = ['dsn', 'user', 'password'];

        foreach (array_keys($config) as $key) {
            if (!in_array($key, $required_keys)) {
                throw new InvalidArgumentException("Required config value missing ($key)");
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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->toArray(),
            'name' => $this->name,
            'config' => $this->config,
        ];
    }

    public static function fromArray(array $data): Database
    {
        return new PDODatabase(
            $data['id'],
            $data['driver'],
            $data['name'],
            $data['config'],
        );
    }
}