<?php

use Websyspro\Entity\Shareds\StructureFile;
use Websyspro\Test\Entitys\BoxEntity;
use Websyspro\Test\Entitys\DocumentEntity;
use Websyspro\Test\Entitys\DocumentItemEntity;

$start = microtime( true );

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

// print_r( $structureFile );

// $start = microtime( true );

// for( $x=0; $x < 1; $x++ ){
//   // BoxEntity::meta( MetaType::Query );
//   // OperatorEntity::meta( MetaType::Query );
//   DocumentEntity::meta( MetaType::Query );
//   // DocumentItemEntity::meta( MetaType::Query );
//   // ProductEntity::meta( MetaType::Query );
//   // ProductGroupEntity::meta( MetaType::Query );
// }

$leftTimer = number_format((microtime( true ) - $start) * 1000, 6, ",", "." );
echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;

// print_r( $structureFile );