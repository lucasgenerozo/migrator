<?php
namespace Lucas\Tcc\Models\Domain;

use Lucas\Tcc\Repositories\Domain\DatabaseRepository;

class Collection
{
    public function __construct(
        private ?int $id,
        private Database $origin,
        private Database $destiny,
    )
    {
    }

    public function getOriginDatabase(): Database
    {
        return $this->origin;
    }

    public function getDestinyDatabase(): Database
    {
        return $this->origin;
    }
}