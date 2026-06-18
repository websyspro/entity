<?php

use Websyspro\Entity\Shareds\Repository;
use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
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
    calcTimer( "Criar Instancia de Repository" );
    $repository = new Repository( PropostaEntity::class );
    calcTimer( "Fim Instancia de Repository" );
    calcTimer( "Chamar metodo Where de Repository" );
    $repository->where( 
      fn( PropostaEntity $p ) => (
        $p->Created >= $dateStart 
        && $p->Created <= $dateEnd 
        && "98%" == $p->NomeProposta
        && !$p->NomeProposta->isNotNull()
        && "EMERSON" != $p->NomeContato
        && !$p->NomeProposta->trim()->upper()->endsWith( $startsWith, "TEST" )
        && $p->NomeProposta->in( "TESTA", "TESTB" )
        // && !$p->itemsProposta->any( fn(ItemPropostaEntity $i) => $i->PropostaId == $p->Id && !$i->IsActive && $p->Created >= $dateStart && $p->Created <= $dateEnd )
        && $p->IsActive == true
      )         
    );   
    
    calcTimer( "Fim metodo Where de Repository" );
    return $repository;
  }
}

if(!defined('MICROTIMER_START')) {
    define('MICROTIMER_START', hrtime(true));
}

$microtimerPrev = MICROTIMER_START;

function calcTimer(
  string $description
): void {
  global $microtimerPrev;
  $now = hrtime(true);

  $total = ($now - MICROTIMER_START) / 1_000_000;
  $delta = ($now - $microtimerPrev) / 1_000_000;

  $microtimerPrev = $now;

  printf( "%-64s: %.6f(ms) +(%.6f)\n", $description, $total, $delta );
}

calcTimer( "Iniciar Processso" );
calcTimer( "Instanciar UserRepository" );
$UserRepository = new UserRepository();
calcTimer( "Fim Instanciar UserRepository" );
calcTimer( "Chamar metodo getAll de UserRepository" );
$getAll = $UserRepository->getAll( "01/01/2024", "31/12/2024", "TERESOPOLIS" );
calcTimer( "Fim metodo getAll de UserRepository" );

echo PHP_EOL;
print_r($getAll);