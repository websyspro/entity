<?php

use Websyspro\Entity\Shareds\ExpressionWhere;
use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Test\Crm\Entitys\PropostaEntity as Props;
use Websyspro\Test\Enums\Status;

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
    $concate = "Test Emerson";
    $escolaA = "Emerson Thiago";
    $escolaB = "COLEGIO CARMOSINA";
    $dateStart = "01/01/2024";
    $dateEnd = "01/31/2024";
    $guid = '0303AE33-D883-43C5-262B-111111111111';

    $perido = new stdClass();
    $perido->start = [ "value" => "01/01/2024" ];
    $perido->end = "01/31/2024";

    $exp = new ExpressionWhere(fn( Props $p ) => (
        $p->Created >= $dateStart
        && $p->Created <= $dateEnd
        // $p->NomeContato == [ "nome da escola: {$escolaB}", Status::Aprovada, Status::Aprovada->value ]
        // && $p->Updated == $perido->end
        // && $p->ComentarioArquivamento == $perido->end
        // && $p->Status == Status::Aprovada
        // && $p->NomeContato == $concate
        // && !$p->IsActive 
        // && 'TEST' >= $p->NomeContato
        // && ( $escolaA == $p->NomeContato && ( $concate == $p->NomeContato )) 
        // && $p->Id == [
        //   $guid,
        //   '0303AE33-D883-43C5-262B-08DBD9497C02',
        //   '0303AE33-D883-43C5-262B-08DBD9497C02',
        //   '0303AE33-D883-43C5-262B-08DBD9497C04',
        //   '0303AE33-D883-43C5-262B-08DBD9497C04',
        //   '0303AE33-D883-43C5-262B-08DBD9497C05',
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
