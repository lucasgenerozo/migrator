<?php
namespace LucasGenerozo\Migrator\Models\Domain;

use Exception;
use InvalidArgumentException;

class Treatment extends Entity
{
    private array $parameters;
    private $function;

    public function __construct(
        ?int $id,
        private string $name,
        private string $parameters_str, 
        private string $function_str
    ) {
        $this->setId($id);
        $this->setParameters($parameters_str);
        $this->setFunction($function_str);
    }

    public function setParameters(string $parameters_str): void
    {
        if (empty($parameters_str)) {
            throw new InvalidArgumentException('Parameter string must have at least one parameter');
        }

        $this->parameters = array_map(
            fn ($param) => trim($param), 
            explode(',', $parameters_str),
        );
    }

    public function setFunction(string $function_str): void
    {
        try {
            $func = "return function ({$this->parameters_str}) {
                {$function_str}
            };";
            $this->function = eval($func);
        } catch (Exception $e) {
            throw new InvalidArgumentException(
                'Invalid function string', 
                400,
                $e
            );
        }
    }

    public function __invoke(...$args): mixed
    {
        if (count($args) != count($this->parameters)) {
            throw new Exception('Parameter count doesnt match');
        }
        
        return ($this->function)(...$args);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parameters' => $this->parameters_str,
            'function' => $this->function_str,
        ];
    }

    public static function fromArray(array $data): Treatment
    {
        return new Treatment(
            $data['id'],
            $data['name'],
            $data['parameters'],
            $data['function'],
        );
    }
}