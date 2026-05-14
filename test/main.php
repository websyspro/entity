<?php

use Websyspro\Commons\Collection;
use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Test\Crm\Entitys\PropostaEntity;
use Websyspro\Entity\Shareds\ExpressionNode;
use Websyspro\Entity\Shareds\ExtractFromFN;
use Websyspro\Test\Enums\Status;

$start = microtime( true );

$escola = "Escola";

$startDate = '01/01/2026';

$closure = fn( PropostaEntity $i ) => (
  $i->NomeProposta === [ 'Item 1', Status::Aprovada, Status::Aprovada, $escola, 12 ]
  && !$i->IsActive
  && $i->NomeProposta === "Test EMERSON THIAGOS"
  && $i->Status === Status::Aprovada
  && $i->IsDeleted <= false 
  && ( $i->PrazoFaturamento === 9098767 && ( $i->PrazoFaturamento === 11191 ))
  && $i->Created >= $startDate
  && $i->IsActive === true 
  && '01/31/2026' >= $i->Created
  && $i->itemsProposta->any( fn( ItemPropostaEntity $o ) => 
    $o->PropostaId === $i->Id && $o->IsActive === true && $o->IsDeleted === false
  )
);




$expressoinNode = new ExpressionNode(
  ExtractFromFN::get( $closure )->tokens, new Collection(), $closure
);

$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;
print_r( $expressoinNode );