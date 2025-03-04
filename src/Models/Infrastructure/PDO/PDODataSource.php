<?php
namespace Lucas\Tcc\Models\Infrastructure\PDO;

use DataBase;
use Lucas\Tcc\Models\Domain\DataSource\ReadableDataSource;
use PDO;

class PDODataSource implements ReadableDataSource
{
    public function __construct(
        private string $name,
        private ?array $with,
        private PDO $pdo
    ) {}


}