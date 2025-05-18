<?php
namespace LucasGenerozo\Migrator\Utils\Providers;

use DI\Container;

class ContainersProvider
{
    public static function getInternalRepositoriesContainer(): Container
    {
        $config = require( __DIR__ . '/../../../config/system.php');

        return new Container(
            $config['persistence']
        );
    }

    public static function getExternalRepositoriesContainer(): Container
    {
        // pegar do file do usuário

        return new Container();
    }
}