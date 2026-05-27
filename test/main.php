<?php

use Websyspro\Test\Crm\Entitys\PropostaEntity;
use Websyspro\Test\Enums\Status;
use Websyspro\Entity\Repository;

$start = microtime( true );

$escolaA = "Minha Escola A";
$escolaB = "Minha Escola B";

$startDate = '01/01/2026';
$concate = "JOIN";

$closure = fn( PropostaEntity $i ) => ( 
  !$i->IsDeleted && $i->Status === Status::Aprovada && $i->NomeProposta === 'MARA%'
  // $i->NomeProposta === [ 'Item 1 ' . $concate, "{$escolaA}", Status::Aprovada->value, Status::RevisaoGerentePendente, $escolaB, 12 ]
  // && $i->NomeProposta->trim()->upper()->contains( 'Item 1 ' . $concate )
  // && $i->NomeProposta === '%TEST'
  // && !$i->IsActive
  // && $i->Status === Status::Aprovada
  // && $i->IsDeleted === false 
  // && ( $i->PrazoFaturamento === 9098767 || ( $i->PrazoFaturamento === 11191 ))
  // && $i->Created >= $startDate
  // && $i->IsActive === true 
  // && '01/31/2026' >= $i->Created
  // && !$i->itemsProposta->any( fn( ItemPropostaEntity $o ) => 
  //   $o->PropostaId === $i->Id && $o->IsActive === true && $o->IsDeleted === false && $i->NomeContato !== 'EMERSON%'
  // )
  // && $i->IsActive === false 
);


$repository = new Repository( PropostaEntity::class );
$repository->where( fn( PropostaEntity $i ) => 
  $i->IsActive && !$i->IsDeleted && $i->ConsultorVendasEspeciaisId != null && $i->NomeProposta === "MARACANAU - Frio - 1"
);
$rows = $repository->all();

$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo "Execute timer: {$leftTimer}(ms)\n";

print_r( $rows );

// var_dump( $expressionWhere->sqlBuild());
// print_r( $expressionWhere );