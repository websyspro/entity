<?php

use Websyspro\Entity\Shareds\ExpressionWhere;
use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Test\Crm\Entitys\PropostaEntity as Props;

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
    $dateStart = "01/01/2024";
    $dateEnd = "01/31/2024";

    $exp = new ExpressionWhere(fn( Props $p ) => (
        !$p->IsActive 
        && $p->Created >= $dateStart
        && 'TEST' >= $p->NomeContato
        && $p->Created <= $dateEnd
        // && ( 'TEST' == $p->NomeContato && ( 'TEST' == $p->NomeContato )) 
        // && $p->Id == [
        //   '0303AE33-D883-43C5-262B-08DBD9497C02',
        //   '0303AE33-D883-43C5-262B-08DBD9497C02',
        //   '0303AE33-D883-43C5-262B-08DBD9497C02',
        //   '0303AE33-D883-43C5-262B-08DBD9497C04',
        //   '0303AE33-D883-43C5-262B-08DBD9497C04',
        //   '0303AE33-D883-43C5-262B-08DBD9497C05'
        // ] 
        // && !$p->itemsProposta->any( fn(ItemPropostaEntity $i ) => (
        //   $i->IsActive == true && $i->IsDeleted == false && $i->PropostaId == $p->Id
        // )) && !$p->IsActive
      )
    );

    return $exp->get();
  }
}

$UserRepository = new UserRepository();
$getAll = $UserRepository->getAll();


$end = (hrtime(true) - $start) / 1_000_000;;
echo "Timer: {$end}(ms)\n";
print_r($getAll);
echo "\nTimer: {$end}(ms)";
