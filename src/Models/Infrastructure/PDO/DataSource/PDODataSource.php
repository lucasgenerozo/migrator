<?php
namespace Lucas\Tcc\Models\Infrastructure\PDO\DataSource;

use Exception;
use Lucas\Tcc\Models\Domain\DataSource\DataSource;
use PDO;

/**
 * O modo do FETCH retornado deve ser definido como default no PDO
 */
class PDODataSource implements DataSource
{
    protected PDO $pdo;

    /**
     * @throws Exception
     */
    public function __construct(
        protected string $name,
        mixed $connection,
        protected ?array $with = null,
        ?array $additional = null,
    ) {
        if (!($connection instanceof PDO)) {
            throw new Exception('CONNECTION MUST BE A PDO INSTANCE');
        }

        $this->pdo = $connection;
    }

    public function listAll(): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
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
        $clauses = [];
        $values = [];
        foreach ($searches as $search) {
            list($column, $operator, $value) = $search;
            $clauses[] = "($column $operator ?)";
            $values[] = $value;
        }
        $clauses_sql = implode(' AND ', $clauses);

        $limit_sql = $limit > 0 ? "LIMIT $limit" : '';

        $stmt = $this->pdo->prepare("
            SELECT *
            FROM   $this->name
            WHERE $clauses_sql
            $limit_sql
        ");
        $stmt->execute($values);

        return $stmt->fetchAll();
    }

    public function firstBy(array $searches): ?array
    {
        return ($this->listBy($searches, 1))[0];
    }

}