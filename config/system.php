<?php

use LucasGenerozo\Migrator\Repositories\Domain\CollectionRepository;

return [

    /**
     * Defina aqui os repositories usado na persistencia do framework, recomendo
     * manter o sqlite mesmo
     */
    'persistence' => [
        CollectionRepository::class => PDOCollectionRepository::class,
    ],

];