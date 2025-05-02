<?php

use Lucas\Tcc\Models\Domain\DataSource\WritableDataSource;
use Lucas\Tcc\Models\Domain\Migration;
use Lucas\Tcc\Models\Infrastructure\PDO\DataSource\PDODataSource;
use Lucas\Tcc\Models\Infrastructure\PDO\DataSource\PDOWritableDataSource;
use Lucas\Tcc\Repositories\Domain\TreatmentRepository;
use Lucas\Tcc\Repositories\Infrastructure\PDOTreatmentRepository;
use PHPUnit\Framework\TestCase;

class MigrationTest extends TestCase
{
    
    public static function emptySqlitePdoCreator(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function sqlitePdoCreator(): PDO
    {
        $pdo = self::emptySqlitePdoCreator();

        $pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT
            );

            CREATE TABLE usuario (
                IdUsuario INTEGER PRIMARY KEY AUTOINCREMENT,
                Nome TEXT
            );
        ");
        $pdo->query("
            INSERT INTO users (id, name) VALUES (1, 'foo'), (2, 'bar'), (3, 'php');
        ");
        
        return $pdo;
    }

    private static function dataSourcesCreator(): array
    {
        $pdo = self::sqlitePdoCreator();
        
        return [
            new PDODataSource(
                'users', 
                $pdo
            ),
            new PDOWritableDataSource(
                'usuario',
                $pdo,
            ),
        ];
    }
    
    private static function tratmentRepositoryCreator(): TreatmentRepository
    {
        $pdo = self::sqlitePdoCreator();
        $pdo->exec("
            CREATE TABLE treatments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                parameters TEXT,
                function TEXT
            );
        ");

        $pdo->query('
            INSERT INTO treatments 
            (id, parameters, function) 
            VALUES 
            (1, "$input, $multiplier", "return $input * $multiplier;"),
            (2, "$input", "return strtoupper($input);");
        ');

        return new PDOTreatmentRepository(
            $pdo,
        );
    }
    
    public static function providerMigrationInicializada(): array
    {
        list($originDataSource, $destinyDataSource) = self::dataSourcesCreator();
        $treatmentRepository = self::tratmentRepositoryCreator();

        $connections = [
            [
                'from' => 'id',
                'to' => 'IdUsuario',
                'treatment' => null,
            ],
            [
                'from' => 'name',
                'to' => 'Nome',
                'treatment' => 2,
            ],
        ];
        
        $migration = new Migration(
            $originDataSource,
            $destinyDataSource,
            $connections,
            $treatmentRepository,
        );

        return [
            [$migration],
        ];
    }
    
    private static function callPrivateMethod(
        mixed $object, 
        string $method, 
        array $args = []
    ): mixed {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
    
        return $method->invokeArgs($object, $args);
    }

    private static function callPrivateProperty(
        mixed $object, 
        string $property
    ): mixed {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
    
        return $property->getValue($object);
    }

    // TODO: Implementar testes
    public function testMigrationNaoDevePermitirQueDestinyDataSourceNaoSejaWritableDataSource(): void
    {
        $this->expectException(TypeError::class);

        list ($originDataSource, ) = self::dataSourcesCreator();
        $treatmentRepository = self::tratmentRepositoryCreator();

        new Migration(
            $originDataSource,
            $originDataSource,
            [],
            $treatmentRepository,
        );
    }

    public function testMigrationNaoDevePermitirInicializacaoSemConnections(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Connections cant be null');

        list ($originDataSource, $destinyDataSource) = self::dataSourcesCreator();
        $treatmentRepository = self::tratmentRepositoryCreator();

        new Migration(
            $originDataSource,
            $destinyDataSource,
            [],
            $treatmentRepository,
        );
    }

    /** @dataProvider providerMigrationInicializada */
    public function testMigrationDeveTerConexaoDefinidaCorretamente(Migration $migration): void
    {   
        $insert_columns = self::callPrivateProperty($migration, 'insert_columns');
        $treatments = self::callPrivateProperty($migration, 'treatments');
        $treatment_columns = self::callPrivateProperty($migration, 'treatment_columns');

        $expected_insert_columns = [
            'id' => 'IdUsuario',
            'name' => 'Nome'
        ];

        $treatmentRepository = self::tratmentRepositoryCreator();
        $expected_treatments = [
            2 => $treatmentRepository->find(2),
        ];

        $expected_treatment_columns = [
            'name' => 2,
        ];

        self::assertEqualsCanonicalizing(
            $expected_insert_columns,
            $insert_columns,
        );
        self::assertEqualsCanonicalizing(
            $expected_treatments,
            $treatments,
        );
        self::assertEqualsCanonicalizing(
            $expected_treatment_columns,
            $treatment_columns,
        );
    }

    /** @dataProvider providerMigrationInicializada */
    public function testMigrationDeveExecutarAMigracaoCorretamente(Migration $migration): void
    {
        $migration->execute();

        /** @var WritableDataSource $destinyDataSource */
        $destinyDataSource = self::callPrivateProperty($migration, 'to');

        self::assertEqualsCanonicalizing(
            [
                [
                    'IdUsuario' => 1,
                    'Nome' => 'FOO',
                ],
                [
                    'IdUsuario' => 2,
                    'Nome' => 'BAR',
                ],
                [
                    'IdUsuario' => 3,
                    'Nome' => 'PHP',
                ],
            ],
            $destinyDataSource->listAll(),
        );
    }
}