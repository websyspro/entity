<?php

use Websyspro\Entity\Shareds\TokensByClosure;
use Websyspro\Test\Crm\Entitys\PropostaEntity;

$start = microtime(true);

class UserRepository
{
  public function __construct(){}

  public function getAll(
  ): array {
    $tokensByClosure = new TokensByClosure(fn( PropostaEntity $i ) => (
        $i->IsActive == true && 
        $i->IsDeleted == false &&
        $i->ConsultorVendasEspeciaisId != null &&
        $i->Id == [ '0303AE33-D883-43C5-262B-08DBD9497C02' ]
      )
    );

    return $tokensByClosure->getClosure();
  }
}

$UserRepository = new UserRepository();
$getAll = $UserRepository->getAll();

$end = ( microtime( true ) - $start ) * 1000;
echo "Timer: {$end}";
print_r($getAll);
