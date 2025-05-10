<?php
namespace Lucas\Tcc\Models\Domain\Migration;

enum MigrationStatus :  int
{
    case Created = 1; 
    case Executing = 2; 
    case Complete = 3; 
}