<?php

use Websyspro\Entity\Shareds\StructureFromFn;
use Websyspro\Test\Entitys\DocumentItemEntity;
use Websyspro\Test\Entitys\DocumentEntity;
use Websyspro\Test\Entitys\OperatorEntity;
use Websyspro\Test\Enums\DocumentState;
use Websyspro\Test\Entitys\BoxEntity;

$fn = fn(
  BoxEntity $box,
  OperatorEntity $operator,
  DocumentEntity $document,
  DocumentItemEntity $documentItem,
) => (
  $box->Id === 101 &&
  $box->Id === $document->BoxId &&
  $box->OperatorId === $operator->Id &&
  $document->Id === $documentItem->DocumentId &&
  $document->CreatedAt >= '02/04/2022' &&
  $document->CreatedAt <= '15/04/2022' &&
  $document->Observations === "Documento cancelado" &&
  $document->Actived === null &&
  $document->State === [ 
    DocumentState::Finalizado, 
    DocumentState::Cancelado
  ]
);

$start = microtime( true );

$arrowFnToString = new StructureFromFn(
  new ReflectionFunction( 
    $fn
  )
);

//print_r($arrowFnToString);
$end = microtime( true );

var_dump( ( $end - $start ) * 1000 );
