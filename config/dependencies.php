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

$builder = new \DI\ContainerBuilder();

$builder->addDefinitions([
    CollectionRepository::class => DI\create(PDOCollectionRepository::class),
    DatabaseRepository::class => DI\create(PDODatabaseRepository::class),
    DatabaseTypeRepository::class => DI\create(PDODatabaseTypeRepository::class),
    MigrationRepository::class => DI\create(PDOMigrationRepository::class),
    TreatmentRepository::class => DI\create(PDOTreatmentRepository::class),
]);

return $builder->build();