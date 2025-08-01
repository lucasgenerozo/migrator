<?php
namespace LucasGenerozo\Migrator\Models\Infrastructure\PDO;

use InvalidArgumentException;
use LucasGenerozo\Migrator\Exceptions\DriverNotSupportedException;
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
    private $driver;

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
        $this->driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
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
            'type_id' => $this->type->getId(),
            'name' => $this->name,
            'config' => json_encode(
                $this->config, 
                flags: JSON_THROW_ON_ERROR
            ),
        ];
    }

    public static function fromArray(array $data): Database
    {
        return new PDODatabase(
            $data['id'],
            $data['type_id'],
            $data['name'],
            $data['config'],
        );
    }

    public function listDataSources(): array
    {
        $dataSourceList = [];
        if ($this->driver == 'sqlite') {
            $stmt = $this->connection->query("
                SELECT name 
                FROM sqlite_master 
                WHERE type='table' 
                AND name NOT LIKE 'sqlite_%'
            ");

            $dataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $dataSourceList = array_column($dataList, 'name');
        } else {
            throw new DriverNotSupportedException("O método 'listDataSource' não foi implementado para o driver '$this->driver'");
        }

        return $dataSourceList;
    }
}