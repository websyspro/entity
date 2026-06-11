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
      && $p->IsActive == true
      && $p->IsDeleted == false
      && $p->CreatedById = '84850ECB-2443-442A-A9B0-4555C9421227'
      && $p->NomeProposta->trim()->upper()->contains( $startsWith )     
    );
    $rep->select(fn( PropostaEntity $p ) => [
      $p->Id, $p->NomeContato, $p->NomeProposta->sum(
        fn(PropostaEntity $p) => $p->DescontoFinalCliente * $p->DescontoFinalCliente
      )
    ]);
    $rep->paged( 1, 1 );

    return $rep->all();
  }
}

$UserRepository = new UserRepository();
$getAll = $UserRepository->getAll( "01/01/2024", "31/12/2024", "TERESOPOLIS" );
print_r($getAll);