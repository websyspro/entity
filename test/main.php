<?php


use Websyspro\Commons\Util as Utilizacao;
use Websyspro\Test\Entitys\DocumentItemEntity;
use Websyspro\Entity\Shareds\StructureFile;
use Websyspro\Test\Entitys\DocumentEntity;
use Websyspro\Test\Entitys\BoxEntity;

$start = microtime( true );

Utilizacao::match("", microtime( true ) - $start );

$call = fn( BoxEntity $box ) => 
  $box->Actived === true &&
  $box->OperatorId === 6 &&
  $box->Operador->Id === 6 || (
    $box->Name === 'THIAGO' &&
    $box->Actived === false
  ) && 
  $box->Documents->where( fn( DocumentEntity $document ) =>
    $document->Actived === true &&
    $document->DocumentItems->where( fn( DocumentItemEntity $documentItem ) => 
      $documentItem->Actived === true && 
      $documentItem->Product->ProductGroup->Name === 'ELETRONICOS'
    )
  ) &&
  $box->Name === 'EMERSON';

$structureFile = new StructureFile(
  new ReflectionFunction( $call )
);
 
$leftTimer = number_format( microtime( true ) - $start, 6, ",", "." );

echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;

print_r( $structureFile );