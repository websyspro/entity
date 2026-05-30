<?php

use Websyspro\Entity\Shareds\WhereByClosure;
use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Test\Crm\Entitys\PropostaEntity as Props;
use Websyspro\Test\Enums\Status;

$start = microtime(true);

class UserRepository
{
  public function __construct(){}

  public function getItems(    
  ): array {
    return [];
  }

  public function getAll(
  ): array {
    $concate = "Test";
    $escolaA = "Emerson";

    $whereByClosure = new WhereByClosure(fn( Props $p ) => (
        $p->IsActive
        && !$p->NomeContato->trim()->startWith( "Test" ) 
        && $p->NomeContato->trim() == 'TEST'
        && ( 'TEST' == $p->NomeContato && ( 'TEST' == $p->NomeContato ))
        && $p->Id == [
          '0303AE33-D883-43C5-262B-08DBD9497C02',
          '0303AE33-D883-43C5-262B-08DBD9497C02',
          '0303AE33-D883-43C5-262B-08DBD9497C02',
          '0303AE33-D883-43C5-262B-08DBD9497C02'
        ]
        && !$p->itemsProposta->any( fn(ItemPropostaEntity $i ) => (
          $i->IsActive == true && $i->IsDeleted == false && $i->PropostaId == $p->Id
        )) && !$p->IsActive
      )
    );

    return $whereByClosure->getClosure();
  }
}

$UserRepository = new UserRepository();
$getAll = $UserRepository->getAll();

$end = ( microtime( true ) - $start ) * 1000;
echo "Timer: {$end}(ms)\n";
print_r($getAll);
