<?php

use Websyspro\Entity\Shareds\StructureFromFn;
use Websyspro\Test\Entitys\DocumentItemEntity;
use Websyspro\Test\Entitys\DocumentEntity;
use Websyspro\Test\Entitys\OperatorEntity;
use Websyspro\Test\Enums\DocumentState;
use Websyspro\Test\Entitys\BoxEntity;

$primeiroAtivo = "Primeiro";
$segundoAtivo = "Segundo";

$fn = fn(
  BoxEntity $box,
  OperatorEntity $operator,
  DocumentEntity $document,
  DocumentItemEntity $documentItem,
) => (
  "%Meu item: {$primeiroAtivo} e \% segundo {$segundoAtivo}" === $box->Id &&
  $box->CreatedBy === $document->BoxId &&
  $box->OperatorId === $operator->Id &&
  $document->Observations === 'Test de Impressão' &&
  $box->Id === 1245 &&
  $document->CreatedAt >= '02/04/2022' &&
  $document->Id === $documentItem->DocumentId &&
  '15/04/2022' >= $document->CreatedAt && (
    $document->Observations === "Documento cancelado" &&
    $document->Actived === null &&
    $document->State === DocumentState::Cancelado &&
    $document->State === [ DocumentState::Finalizado, DocumentState::Cancelado, $segundoAtivo ]
  )
);

$start = microtime( true );

$arrowFnToString = new StructureFromFn(
  new ReflectionFunction( 
    $fn
  )
);

print_r( $arrowFnToString->joins );

$end = microtime( true );

var_dump( ( $end - $start ) * 1000 );

// $tokens = [
//   'Meu item: {$primeiroAtivo} e segundo {$segundoAtivo}',
//   'Test de Impressão',
//   '02/04/2022',
//   '15/04/2022',
//   "Documento cancelado",
//   '(DocumentState::Finalizado,DocumentState::Cancelado,$segundoAtivo)'
// ];

// $pattern = "#'[^']*'|\"[^\"]*\"|\\{\\$[\\w-]+\\}|\\$?[\\w\\\\-]+(?:->|::)[\\w\\\\-]+|\\d{2}/\\d{2}/\\d{4}|>=|<=|<>|[<>=!]+|\\(|\\)|,|([a-zA-ZÀ-ÿ\d/:$]+(?:\s+[a-zA-ZÀ-ÿ\d/:$]+)*\s*)#u";

// foreach( $tokens as $token ){
//   preg_match_all(
//     $pattern,
//     $token,
//     $results
//   );

//   print_r($results);
// }