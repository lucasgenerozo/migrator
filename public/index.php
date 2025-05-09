<?php

use Lucas\Tcc\Models\Domain\Interpreter;

$container = require_once( __DIR__ . '/../config/dependencies.php');

$id_collection = $_GET['id'];

(
    new Interpreter(
        $container,
        $id_collection,
    )
)->execute();