<?php
namespace Lucas\Tcc\Exceptions;

use Exception;

class ResourceNotFound extends Exception
{
    public function __construct(
        string $resource_name,
        array $search = [],
    )
    {
        list($column, $operator, $value) = $search;

        $message = "Resource '$resource_name' not found";
        if (!empty($search)) {
            $message .= " using '$column $operator \"$value\"' clause";
        }        

        parent::__construct(
            $message,
            400,
        );
    }
}