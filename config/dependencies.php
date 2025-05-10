<?php

use Lucas\Tcc\Repositories\Domain\CollectionRepository;
use Lucas\Tcc\Repositories\Domain\DatabaseRepository;
use Lucas\Tcc\Repositories\Domain\DatabaseTypeRepository;
use Lucas\Tcc\Repositories\Domain\MigrationRepository;
use Lucas\Tcc\Repositories\Domain\TreatmentRepository;
use Lucas\Tcc\Repositories\Infrastructure\PDOCollectionRepository;
use Lucas\Tcc\Repositories\Infrastructure\PDODatabaseRepository;
use Lucas\Tcc\Repositories\Infrastructure\PDODatabaseTypeRepository;
use Lucas\Tcc\Repositories\Infrastructure\PDOMigrationRepository;
use Lucas\Tcc\Repositories\Infrastructure\PDOTreatmentRepository;
use Psr\Container\ContainerInterface;

$builder = new \DI\ContainerBuilder();

$builder->addDefinitions([
    PDO::class => function() {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("
            CREATE TABLE treatments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                parameters TEXT,
                function TEXT
            );

            CREATE TABLE databases (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                type_id INTEGER,
                name TEXT,
                config TEXT
            );

            CREATE TABLE database_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                writable INTEGER /* 1 = SIM, 0 = NAO */
            );

            CREATE TABLE collections (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                database_origin INTEGER,
                database_destiny INTEGER
            );

            CREATE TABLE migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                collection_id INTEGER,
                json TEXT,
                status INTEGER
            );
        ");
        $pdo->query('
            INSERT INTO treatments (id, name, parameters, function) VALUES
            (123, "multiplier", "$input, $multiplier", "return $input * $multiplier;");
        ');
        $pdo->query("
            INSERT INTO database_types (id, name, writable) VALUES 
            (1, 'JSON', 0),
            (2, 'SQL', 1),
            (3, 'Mongo', 1); 
        ");
        $pdo->query("
            INSERT INTO databases (id, type_id, name, config) VALUES
            (1, 1, 'Dump importado', '{\"dsn\": \"sqlite::memory:\",\"user\":\"\",\"password\":\"\"}'),
            (2, 2, 'Origem', '{\"dsn\": \"sqlite::memory:\",\"user\":\"\",\"password\":\"\"}'),
            (3, 2, 'Destino', '{\"dsn\": \"sqlite::memory:\",\"user\":\"\",\"password\":\"\"}'); 
        ");
        $pdo->query("
            INSERT INTO collections (id, database_origin, database_destiny) VALUES
            (1, 2, 3),
            (2, 1, 2);
        ");

        $json = '{"from": {"identifier": "pessoa","clauses": [{"field": "IdInstituicao","operator": "=","value": "51"},{"field": "Excluido","operator": "=","value": "N"}],"with": null},"to": {"identifier": "pessoas","clauses": null,"with": null},"connections": [{"from": "IdPessoa","to": "id_origem","treatment": 123},{"from": "NomeRazaoSocial","to": "nome","treatment": null}]}';

        $pdo->query("
            INSERT INTO migrations (id, collection_id, json, status) VALUES
            (1, 1, '$json', 1),
            (2, 1, '$json', 1);
        ");

        return $pdo;
    },
    DatabaseTypeRepository::class => DI\autowire(PDODatabaseTypeRepository::class),
    TreatmentRepository::class => DI\autowire(PDOTreatmentRepository::class),
    DatabaseRepository::class => DI\autowire(PDODatabaseRepository::class),
    MigrationRepository::class => DI\autowire(PDOMigrationRepository::class),
    CollectionRepository::class => DI\autowire(PDOCollectionRepository::class),
]);

return $builder->build();