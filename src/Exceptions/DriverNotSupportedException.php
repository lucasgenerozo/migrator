<?php
namespace LucasGenerozo\Migrator\Exceptions;

use DomainException;

class DriverNotSupportedException extends DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
