<?php

use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Entity\Shareds\ExpressionWhere;
use Websyspro\Entity\Shareds\WhereByClosure;
use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Test\Crm\Entitys\PropostaEntity as Props;
use Websyspro\Test\Entitys\BoxEntity;
use Websyspro\Test\Enums\Status;
use function Websyspro\Entity\Shareds\initWhere;

$start = hrtime(true);

class UserRepository
{
  public function __construct(){}

  public function getItems(    
  ): array {
    return [];
  }

  public function getAll(
  ): mixed {
    $concate = "Test";
    $escolaA = "Emerson";

    $exp = new ExpressionWhere(fn( Props $p ) => (
        !$p->IsActive 
        && 'TEST' >= $p->NomeContato
        && ( 'TEST' == $p->NomeContato && ( 'TEST' == $p->NomeContato )) 
        && $p->Id == [
          '0303AE33-D883-43C5-262B-08DBD9497C02',
          '0303AE33-D883-43C5-262B-08DBD9497C02',
          '0303AE33-D883-43C5-262B-08DBD9497C02',
          '0303AE33-D883-43C5-262B-08DBD9497C04',
          '0303AE33-D883-43C5-262B-08DBD9497C04',
          '0303AE33-D883-43C5-262B-08DBD9497C05'
        ] 
        && !$p->itemsProposta->any( fn(ItemPropostaEntity $i ) => (
          $i->IsActive == true && $i->IsDeleted == false && $i->PropostaId == $p->Id
        )) && !$p->IsActive
      )
    );

    return $exp->get();
  }
}

// $UserRepository = new UserRepository();
// $getAll = $UserRepository->getAll();

$entityStructure = new EntityStructure(BoxEntity::class);
$get = $entityStructure->get();

$end = (hrtime(true) - $start) / 1_000_000;;
echo "Timer: {$end}(ms)\n";
print_r($get);
// print_r($getAll);
echo "\nTimer: {$end}(ms)";
