<?php

use Websyspro\Entity\Shareds\ClosureUtil;
use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Test\Crm\Entitys\PropostaEntity;
use Websyspro\Entity\Shareds\ExpressionWhere;
use Websyspro\Test\Enums\Status;

$start = microtime( true );

$escolaA = "Minha Escola A";
$escolaB = "Minha Escola B";

$startDate = '01/01/2026';
$concate = "JOIN";

$closure = fn( PropostaEntity $i ) => (
  $i->NomeProposta !== [ 'Item 1 ' . $concate, "{$escolaA}", Status::Aprovada->value, Status::RevisaoGerentePendente, $escolaB, 12 ]
  && !$i->IsActive
  && $i->Status === Status::Aprovada
  && $i->IsDeleted === false 
  && ( $i->PrazoFaturamento === 9098767 || ( $i->PrazoFaturamento === 11191 ))
  && $i->Created >= $startDate
  && $i->IsActive === true 
  && '01/31/2026' >= $i->Created
  && !$i->itemsProposta->any( fn( ItemPropostaEntity $o ) => 
    $o->PropostaId === $i->Id && $o->IsActive === true && $o->IsDeleted === false
  )
  && $i->IsActive === false 
);

//print_r(ExtractFromFN::get( $closure )->tokens);

// $expressoinNode = new ExpressionNode(
//   ExtractFromFN::get( $closure )->tokens, new Collection(), $closure
// );

// for( $i = 0; $i <= 50; $i++ ){
//   ClosureUtil::getEntityStructure( PropostaEntity::class );
// }

$expressionWhere = new ExpressionWhere( $closure );

$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;

print_r( $expressionWhere );