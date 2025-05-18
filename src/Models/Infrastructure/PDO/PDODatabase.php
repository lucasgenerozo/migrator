<?php
namespace LucasGenerozo\Migrator\Models\Infrastructure\PDO;

use InvalidArgumentException;
use LucasGenerozo\Migrator\Models\Domain\Database\Database;
use LucasGenerozo\Migrator\Models\Domain\Database\DatabaseType;
use LucasGenerozo\Migrator\Models\Domain\DataSource\DataSource;
use LucasGenerozo\Migrator\Models\Domain\Entity;
use LucasGenerozo\Migrator\Models\Infrastructure\PDO\DataSource\PDODataSource;
use LucasGenerozo\Migrator\Models\Infrastructure\PDO\DataSource\PDOWritableDataSource;
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
        $this->setConnectionByConfig($config);
    }

    private function setConnectionByConfig(array $config): void
    {
        $required_keys = ['dsn', 'user', 'password'];
        $config_keys = array_keys($config);

        foreach ($required_keys as $key) {
            if (!in_array($key, $config_keys)) {
                throw new InvalidArgumentException("Required config value missing ($key)");
            }
        } 

        $this->connection = new PDO(
            $config['dsn'],
            $config['user'],
            $config['password'],
        );
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDataSource(string $name, ?array $with = null): ?DataSource
    {
        if ($this->type->isWritable()) {
            return new PDOWritableDataSource(
                $name,
                $this->connection,
                $with
            );
        }
        
        return new PDODataSource(
            $name,
            $this->connection,
            $with
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
            $data['type'],
            $data['name'],
            $data['config'],
        );
    }
}