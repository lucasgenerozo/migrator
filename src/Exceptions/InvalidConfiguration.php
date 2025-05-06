<?php
namespace Lucas\Tcc\Exceptions;

use Exception;

class InvalidConfiguration extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}