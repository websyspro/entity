<?php

use Websyspro\Commons\Collection;
use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Test\Crm\Entitys\PropostaEntity;
use Websyspro\Entity\Shareds\ExpressionNode;
use Websyspro\Entity\Shareds\ExtractFromFN;
use Websyspro\Test\Crm\Entitys\ObraEntity;
use Websyspro\Test\Enums\Status;

$start = microtime( true );

$test = "Escola";

$startDate = '01/01/2026';

$fn = fn( PropostaEntity $i ) => (
  !$i->IsActive
  && $i->IsDeleted === false 
  && $i->Status === Status::Aprovada
  && ( $i->PrazoFaturamento === 9098767 && ( $i->Status === "teste" ))
  && $i->Created >= $startDate
  && $i->NomeProposta === "Teste {$test}"
  && $i->IsActive === true 
  && '01/31/2026' >= $i->Created
  && $i->itemsProposta->any( fn( ItemPropostaEntity $o ) => 
    $o->PropostaId === $i->Id && $o->IsActive === true && $o->IsDeleted === false
  )
  && $i->itemsProposta->any( fn( ItemPropostaEntity $o ) => 
    $o->IsActive && !$o->IsDeleted && $o->PropostaId === $i->Id
  )
);


$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;

$expressoinNode = new ExpressionNode(
  ExtractFromFN::get( $fn )->tokens, new Collection()
);

print_r( $expressoinNode );