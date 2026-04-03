<?php

use Websyspro\Entity\Repository;
use Websyspro\Test\Entitys\BoxEntity;
use Websyspro\Test\Entitys\DocumentEntity;
use Websyspro\Test\Entitys\DocumentItemEntity;

$start = microtime( true );

$staticTest = "THIAGO"; 

$repository = new Repository(
  BoxEntity::class
);

$repository->where( fn( BoxEntity $box ) => 
  $box->Actived === true &&
  $box->OperatorId === 6 &&
  $box->CreatedAt >= '12/05/2024' &&
  $box->Operador->Id === 6 || (
    $box->Name === $staticTest &&
    $box->Actived === false
  ) && 
  $box->Documents->where( fn( DocumentEntity $document ) =>
    $document->Actived === true &&
    $document->DocumentItems->where( fn( DocumentItemEntity $documentItem ) => 
      $documentItem->Actived === true && 
      $documentItem->Product->ProductGroup->Name === 'ELETRONICOS'
    )
  ) &&
  $box->Name === 'EMERSON' &&
  $box->CreatedAt <= '18/05/2024'
);

$repository->queryBuilder();

$leftTimer = number_format((microtime( true ) - $start) * 1000, 6, ",", "." );
echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;