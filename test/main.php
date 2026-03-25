<?php


use Websyspro\Entity\Shareds\StructureFile;
use Websyspro\Test\Entitys\BoxEntity;
use Websyspro\Test\Entitys\DocumentEntity;
use Websyspro\Test\Entitys\DocumentItemEntity;

$call = fn( BoxEntity $box ) => 
  $box->Actived === true &&
  $box->OperatorId === 6 &&
  $box->Documents->where( fn( DocumentEntity $document ) =>
    $document->Actived === true &&
    $document->DocumentItems->where( fn( DocumentItemEntity $documentItem ) =>  
      $documentItem->Product->ProductGroup->Name === 'ELETRONICOS'
    )
  );

$structureFile = new StructureFile(
  new ReflectionFunction( $call )
);

print_r( $structureFile );