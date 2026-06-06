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
      && $p->ComentarioArquivamento->contains( 'DECLINOU' )     
    ));
    $rep->paged( 1, 6 );
    return $rep->all();
  }
}

$UserRepository = new UserRepository();
$getAll = $UserRepository->getAll("30/10/2023", "30/10/2023");
print_r($getAll);