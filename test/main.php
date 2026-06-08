<?php

use Websyspro\Entity\Repository;
use Websyspro\Test\Crm\Entitys\PropostaEntity;

class UserRepository
{
  public function __construct(){}

  public function getItems(    
  ): array {
    return [];
  }

  public function getAll(
    string $dateStart,
    string $dateEnd,
    string $startsWith
  ): mixed {
    $rep = new Repository( PropostaEntity::class );
    $rep->where( fn( PropostaEntity $p ) => 
      $p->Created >= $dateStart 
      && $p->Created <= $dateEnd
      && $p->IsActive 
      && !$p->IsDeleted
      && $p->CreatedById = '84850ECB-2443-442A-A9B0-4555C9421227'
      && !$p->NomeProposta->trim()->upper()->contains( "TESTE", "TEST" )     
    );
    $rep->select( fn( PropostaEntity $p ) => [
      $p->Id
    ]);
    $rep->paged( 1, 2 );
    
    return $rep->all();
  }
}

$UserRepository = new UserRepository();
$getAll = $UserRepository->getAll( "01/01/2024", "31/12/2024", "TEST" );
print_r($getAll);