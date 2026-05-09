<?php

use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Test\Crm\Entitys\ValoresObraEntity;
use Websyspro\Test\Crm\Entitys\PropostaEntity;
use Websyspro\Test\Crm\Entitys\ObraEntity;
use Websyspro\Entity\Repository;
use Websyspro\Test\Enums\Status;

$start = microtime( true );

$startDate = '01/01/2026';

$repo = new Repository( PropostaEntity::class );
$repo
  // ->include( fn( PropostaEntity $p ) => $p->itemsProposta->where( fn( ItemPropostaEntity $i ) => $i->IsActive === true )
  //   ->include( fn( ItemPropostaEntity $i ) => $i->obra
  //     ->include( fn( ObraEntity $i ) => $i->ValoresObra->where( fn( ValoresObraEntity $v ) => $v->IsActive === true ))
  //   )
  // )
  ->where( fn( PropostaEntity $i ) => 
      $i->IsActive === true 
      && $i->IsDeleted === false 
      && $i->Status === Status::Aprovada
      && $i->PrazoFaturamento === 90
      && $i->itemsProposta->any( fn( ItemPropostaEntity $o ) => 
        $o->PropostaId === $i->Id 
        && $o->IsActive === true 
        && $o->IsDeleted === false
      )
      && $i->itemsProposta->any( fn( ItemPropostaEntity $o ) => 
        $o->IsActive && !$o->IsDeleted && $o->PropostaId === $i->Id
      ) 
  )
  ->select( fn( PropostaEntity $i ) => [ 
    $i->Id, $i->NomeProposta, $i->itemsProposta->sum(
      fn( ItemPropostaEntity $s ) => $s->ValorUnitario * $s->Quantidade
    ) 
  ]);


$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;

print_r( $repo->all() );