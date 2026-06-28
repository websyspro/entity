<?php

use Websyspro\Entity\Shareds\Repository;
use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Test\Crm\Entitys\PropostaEntity;
use Websyspro\Test\Enums\Status;

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
    $repository = new Repository( PropostaEntity::class );
    $repository->where( 
      fn( PropostaEntity $p ) => 
        !$p->IsActive
        && $p->ConsultorVendasEspeciaisId
        && $p->Status == Status::Aprovada
        && "98%" == $p->NomeProposta
        && !$p->NomeProposta->isNotNull()
        && !$p->NomeProposta->in( $startsWith, "TESTB" )
        && $p->Created >= $dateStart 
        && $p->Created <= $dateEnd
        && "EMERSON" != $p->NomeContato
        && !$p->NomeProposta->trim()->upper()->endsWith( $startsWith, "TEST" )
        && !$p->itemsProposta->any( fn(ItemPropostaEntity $i) => $i->PropostaId == $p->Id && !$i->IsActive && $p->Created >= $dateStart && $p->Created <= $dateEnd )
    );
    $repository->select( fn(PropostaEntity $p) => [ 
      $p->DescontoFinalCliente,
      $p->DistribuidorId,
      $p->sum( $p->DescontoFinalCliente * $p->DescontoFinalCliente)
    ]);   
    return $repository->get();
  }
}

if(!defined('MICROTIMER_START')) {
    define('MICROTIMER_START', hrtime(true));
}

function calcTimer(
  string $description
): void {
  $now = hrtime(true);
  $total = ($now - MICROTIMER_START) / 1_000_000;
  $microtimerPrev = $now;

  printf( "%s: %.6f(ms)\n", $description, $total );
}

calcTimer( "Iniciar Processso" );
$UserRepository = new UserRepository();
$getAll = $UserRepository->getAll( "01/01/2024", "31/12/2024", "TERESOPOLIS" );
calcTimer( "Fim Processso" );

echo PHP_EOL;
print_r($getAll);