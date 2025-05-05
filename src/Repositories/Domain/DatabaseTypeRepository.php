<?php
namespace Lucas\Tcc\Repositories\Domain;

interface DatabaseTypeRepository
{
    public function list(): ?array;
}