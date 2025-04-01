<?php
namespace Lucas\Tcc\Models\Infrastructure\PDO;

use Lucas\Tcc\Models\Domain\DataSource\DataSource;
use PDO;

class PDODataSource implements DataSource
{
    public function __construct(
        private string $name,
        private ?array $with,
        private PDO $pdo
    ) {}

    public function listAll(): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM   $this->name
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByCallback(callable $callback): ?array
    {

    }

    public function findBy(string $label, mixed $value): mixed
    {

    }


}