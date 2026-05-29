<?php

use Websyspro\Commons\Shareds\TokensByClosure;
use Websyspro\Test\Crm\Entitys\PropostaEntity;
use Websyspro\Entity\Repository;

class UserRepository
{
  public function __construct(){}

  public function getAll(
  ): array {
    $tokensByClosure = new TokensByClosure(
      fn( PropostaEntity $i ) => (
        $i->IsActive == true && 
        $i->IsDeleted == false &&
        $i->ConsultorVendasEspeciaisId != null &&
        $i->Id == [ '0303AE33-D883-43C5-262B-08DBD9497C02' ]
      )
    );

    return [];
  }
}

$repository = new Repository( PropostaEntity::class );
$repository->where( fn( PropostaEntity $i ) =>
  $i->IsActive == true && 
  $i->IsDeleted == false &&
  $i->ConsultorVendasEspeciaisId != null &&
  $i->Id == [ '0303AE33-D883-43C5-262B-08DBD9497C02' ]

  // $i->ConsultorVendasEspeciaisId != null && 
  // $i->NomeProposta == "MARACANAU - Frio - 1" && 
  // $i->NomeContato == 'IANA%' &&
  // $i->ConsultorVendasEspeciaisId != null &&
  // $i->ContatoId == null &&
  // $i->Created >= $createAt

  // && $i->Created >= $startDate && '01/31/2026' >= $i->Created
  // $i->NomeProposta === [ 'Item 1 ' . $concate, "{$escolaA}", Status::Aprovada->value, Status::RevisaoGerentePendente, $escolaB, 12 ]
  // && $i->NomeProposta->trim()->upper()->contains( 'Item 1 ' . $concate )
  // && $i->NomeProposta === '%TESTEMERSON'
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
$rows = $repository->all();

$timerEnd = ( microtime( true ) - $timerStart ) * 1000;
echo json_encode([ "timer" => $timerEnd, "rows" => $rows ]);
