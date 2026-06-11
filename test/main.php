<?php

use Websyspro\Entity\Shareds\ExpressionWhere;
use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Test\Crm\Entitys\PropostaEntity;

$microTimerStart = microtime(true);

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
    $expressionWhere = new ExpressionWhere(
      fn( PropostaEntity $p ) => (
        $p->Created >= $dateStart 
        && $p->Created <= $dateEnd
        && $p->IsActive == true
        && $p->IsDeleted == false
        && $p->CreatedById = '84850ECB-2443-442A-A9B0-4555C9421227'
        && $p->NomeProposta->trim()->upper()->contains( $startsWith )
        && !$p->itemsProposta->any( fn(ItemPropostaEntity $i) => $i->PropostaId == $p->Id && $i->IsActive )
      ) 
    );    

    return $expressionWhere;
  }
}

$UserRepository = new UserRepository();
$getAll = $UserRepository->getAll( "01/01/2024", "31/12/2024", "TERESOPOLIS" );

$microTimerEnd = microtime(true);
printf( "%f(ms)\n", bcmul( bcsub( $microTimerEnd, $microTimerStart, 4 ), 1000, 4 ));
print_r($getAll);