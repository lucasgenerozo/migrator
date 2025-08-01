<?php
namespace LucasGenerozo\Migrator\Models\Infrastructure\PDO\DataSource;

use Exception;
use LucasGenerozo\Migrator\Exceptions\DriverNotSupportedException;
use LucasGenerozo\Migrator\Exceptions\InvalidConfiguration;
use LucasGenerozo\Migrator\Models\Domain\DataSource\DataSource;
use LucasGenerozo\Migrator\Utils\SQLOperator;
use PDO;
use TypeError;

/**
 * O modo do FETCH retornado deve ser definido como default no PDO
 * 
 */
class PDODataSource implements DataSource
{
    protected PDO $pdo;
    private $driver;


    public function __construct(
        protected string $name,
        mixed $connection,
        protected ?array $with = null,
        ?array $additional = null,
    ) {
        $this->setPdo($connection);
    }

    /**
     * Como a interface DataSource nao pode especificar o tipo da connection, ela tem que ser validada nas implementações
     * 
     * @throws TypeError
     * @throws InvalidConfiguration
     */
    private function setPdo(mixed $connection): void
    {
        if (!($connection instanceof PDO)) {
            throw new TypeError('Connection must be a PDO instance');
        }

        $pdo_fetch_mode = $connection->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
        if ($pdo_fetch_mode != PDO::FETCH_ASSOC) {
            throw new InvalidConfiguration("PDO fetch mode must be 'ASSOC'");
        }

        $this->pdo = $connection;
        $this->driver = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function listAll(): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT $this->name.*
            FROM   $this->name
        ");
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listByCallback(callable $callback): ?array
    {
        return array_filter($this->listAll(), $callback);
    }

    public function listBy(array $searches, int $limit = 0): ?array
    {
        $sql_operator = new SQLOperator();
        list($clauses_sql, $values) = $sql_operator->searchesToWhere($searches);

        $limit_sql = $limit > 0 ? "LIMIT $limit" : '';

        $stmt = $this->pdo->prepare("
            SELECT $this->name.*
            FROM   $this->name
            WHERE $clauses_sql
            $limit_sql
        ");
        $stmt->execute($values);

        return $stmt->fetchAll();
    }

    public function firstBy(array $searches): ?array
    {
        $result = $this->listBy($searches, 1);
        
        if (empty($result)) {
            return null;
        }

        return $result[0];
    }

    public function listColumns(): array
    {
        $columnDataList = [];

        if ($this->driver == 'sqlite') {
            $stmt = $this->pdo->query("PRAGMA table_info($this->name)");
            $result = $stmt->fetchAll();
            $columnDataList = array_column($result, 'name');
        } else {
            throw new DriverNotSupportedException("O método 'listDataSource' não foi implementado para o driver '$this->driver'");
        }

        return $columnDataList;
    }

}