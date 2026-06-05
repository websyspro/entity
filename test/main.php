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
    string $dateEnd
  ): mixed {
    $rep = new Repository( PropostaEntity::class );
    $rep->where( fn( PropostaEntity $p ) => (
      $p->Created >= $dateStart 
      && $p->Created <= $dateEnd
      && $p->NomeProposta->contains( 'SAO JOAO DA PONTE-MG - 2' )     
    ));
    $rep->paged( 1, 6 );
    return $rep->all();
  }
}

$UserRepository = new UserRepository();
$getAll = $UserRepository->getAll("01/01/2024", "31/03/2024");

// header('Content-Type: application/json; charset=utf-8');
// echo json_encode($getAll);
print_r($getAll);