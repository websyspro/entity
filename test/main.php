<?php

use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Entity\Shareds\AbstractRepository;
use Websyspro\Test\Crm\Entitys\PropostaEntity;
use Websyspro\Test\Crm\Entitys\ObraEntity;
use Websyspro\Test\Crm\Entitys\ValoresObraEntity;

$start = microtime( true );

enum FlagType:int {
  case Actived = 1;
  case Inactive = 0;
} 

$startDate = '01/01/2026';

$repo = new AbstractRepository( PropostaEntity::class );
$repo
  ->include( fn( PropostaEntity $p ) => $p->itemsProposta->where( fn( ItemPropostaEntity $i ) => $i->IsActive && $i->IsDeleted === true )
    ->include( fn( ItemPropostaEntity $i ) => $i->obra
      ->include( fn( ObraEntity $i ) => $i->ValoresObra->where( fn( ValoresObraEntity $v ) => $v->CmvSomosPercentual === 0.78 ))
    )
  )
  ->where( fn( PropostaEntity $i ) => 
      $i->IsActive === FlagType::Actived && 
     !$i->IsDeleted && (
      $i->Created >= $startDate &&
      $i->IsActive === true && 
      '01/31/2026' >= $i->Created && 
     !$i->IsActive && 
      $i->itemsProposta->any( fn( ItemPropostaEntity $o ) => 
        $o->Amortizacao === 145.89 && !$o->IsDeleted && $o->PropostaId === $i->Id
      ) && 
      $i->itemsProposta->any( fn( ItemPropostaEntity $o ) => 
        $o->IsActive && !$o->IsDeleted && $o->PropostaId === $i->Id
      ) && 
      $i->Status === "Ativo" 
    )
  )->select( fn( PropostaEntity $i ) => [ 
    $i->Id, $i->NomeProposta, $i->itemsProposta->sum(
      fn( ItemPropostaEntity $s ) => $s->ValorUnitario * $s->Quantidade
    ) 
  ]);


$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;

print_r( $repo->getStructure() );