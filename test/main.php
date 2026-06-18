<?php

use Websyspro\Entity\Shareds\Repository;
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
    $repository = new Repository( PropostaEntity::class );
    calcTimer( "instanciar Class Repository" );
    $repository->where( fn( PropostaEntity $p ) => $p->NomeProposta == "98%" );
    calcTimer( "Execute Repository::where" );
    $repository->select( fn( PropostaEntity $p ) => $p->NomeProposta );
    calcTimer( "Execute Repository::select" );
    $repository->orderBy( fn( PropostaEntity $p ) => $p->NomeProposta );
    calcTimer( "Execute Repository::orderBy" );    
    return $repository;
    // $expressionWhere = new ExpressionAbstract_(
    //   fn( PropostaEntity $p ) => (
    //     "98%" == $p->NomeProposta
    //     && !$p->NomeProposta->isNotNull()
    //     && $p->Created >= $dateStart 
    //     && $dateEnd >= $p->Created
    //     && "EMERSON" != $p->NomeContato
    //     && !$p->NomeProposta->trim()->upper()->endsWith( $startsWith, "TEST" )
    //     && $p->NomeProposta->in( "TESTA", "TESTB" )
    //     && $p->IsActive == true
    //     && !$p->IsActive
    //     && '84850ECB-2443-442A-A9B0-4555C9421227' == $p->CreatedById
    //     && $p->IsDeleted == false
    //     && $p->NomeProposta->trim()->upper() == "98%"
    //     && !$p->itemsProposta->any( fn(ItemPropostaEntity $i) => $i->PropostaId == $p->Id && !$i->IsActive && $p->Created >= $dateStart && $p->Created <= $dateEnd )
    //   ) 
    // );

    // $expressionWhere->get();
    // return $expressionWhere;
  }
}

if(!defined( "MICROTIMER_START" )) 
  define( "MICROTIMER_START", microtime( true ));

function calcTimer( string $description ): void {
  printf( "%s: %f(ms)\n",
    str_pad( $description, 64, ".", STR_PAD_RIGHT ), 
    bcmul( bcsub( microtime(true), MICROTIMER_START, 6 ), 1000, 6 )
  );
}

calcTimer( "Iniciar Processso" );

$UserRepository = new UserRepository();
calcTimer( "Instanciar classe UserRepository" );

$getAll = $UserRepository->getAll( "01/01/2024", "31/12/2024", "TERESOPOLIS" );
calcTimer( "Chamar Metodo GetAll from UserRepository" );

calcTimer( "Tempo total" );
echo PHP_EOL;
print_r($getAll);