<?php

use Dom\Document;
use Websyspro\Entity\Repository;
use Websyspro\Entity\Shareds\StructureFromFn;
use Websyspro\Test\Connect\DB;
use Websyspro\Test\Entitys\DocumentItemEntity;
use Websyspro\Test\Entitys\DocumentEntity;
use Websyspro\Test\Entitys\OperatorEntity;
use Websyspro\Test\Enums\DocumentState;
use Websyspro\Test\Entitys\BoxEntity;

$primeiroAtivo = "Primeiro";
$segundoAtivo = "Segundo";

$start = microtime( true );

// $repository = new Repository( BoxEntity::class );
// $repository->where( fn(
//   BoxEntity $box,
//   OperatorEntity $operator,
//   DocumentEntity $document,
//   DocumentItemEntity $documentItem,
// ) => (
//   "%Meu item: {$primeiroAtivo} e \% segundo {$segundoAtivo}" === $box->Id &&
//   $box->CreatedBy === $document->BoxId &&
//   $box->OperatorId === $operator->Id &&
//   $document->Observations === 'Test de Impressão' &&
//   $box->Id === 1245 &&
//   $document->CreatedAt >= '02/04/2022' &&
//   $document->Id === $documentItem->DocumentId &&
//   '15/04/2022' >= $document->CreatedAt && (
//     $document->Observations === "Documento cancelado" &&
//     $document->Actived === null &&
//     $document->State === DocumentState::Cancelado &&
//     $document->State === [ DocumentState::Finalizado, DocumentState::Cancelado, $segundoAtivo ]
//   )
// ));

$repository = new Repository( DocumentEntity::class );
$repository->where( fn(
  DocumentEntity $document,
  DocumentItemEntity $documentItem
) => (
  $document->Observations === "%Meu item: {$primeiroAtivo} e \% segundo {$segundoAtivo}" &&
  $document->CreatedAt >= '02/04/2023' &&
  $document->Id === 100 &&
  '10/04/2023' >= $document->CreatedAt &&
  $document->State = DocumentState::Cancelado && (
    $documentItem->DocumentId = $document->Id &&
    $document->State === [ DocumentState::Finalizado, DocumentState::Cancelado, $segundoAtivo ]
  )
));

$repository->queryBuilder();

// print_r( $repository->structureFromFn->tokens );

// echo $repository->sql->first();

// $query = DB::queryWithPrepared( $repository->sql->first(), $repository->prepareds);
// print_r( $query );



var_dump( ( microtime( true ) - $start ) * 1000 );

// $tokens = [
//   'Meu item: {$primeiroAtivo} e segundo {$segundoAtivo}',
//   'Test de Impressão',
//   '02/04/2022',
//   '15/04/2022',
//   "Documento cancelado",
//   '(DocumentState::Finalizado,DocumentState::Cancelado,$segundoAtivo)'
// ];

// ^\(([A-Za-z0-9_]+)(,[A-Za-z0-9_]+)*\)$

// $pattern = "#'[^']*'|\"[^\"]*\"|\\{\\$[\\w-]+\\}|\\$?[\\w\\\\-]+(?:->|::)[\\w\\\\-]+|\\d{2}/\\d{2}/\\d{4}|>=|<=|<>|[<>=!]+|\\(|\\)|,|([a-zA-ZÀ-ÿ\d/:$]+(?:\s+[a-zA-ZÀ-ÿ\d/:$]+)*\s*)#u";

// foreach( $tokens as $token ){
//   preg_match_all(
//     $pattern,
//     $token,
//     $results
//   );

//   print_r($results);
// }