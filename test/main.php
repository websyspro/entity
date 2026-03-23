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
use Websyspro\Test\Entitys\CustomerEntity;
use Websyspro\Test\Entitys\ProductEntity;
use Websyspro\Test\Entitys\ProductGroupEntity;

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

// $repository = new Repository( DocumentEntity::class );
// $repository->where( fn(
//   DocumentEntity $document,
//   // DocumentItemEntity $documentItem
// ) => (
//   $document->Id !== [ 301, 302, 405 ]
// ));

// $repository->queryBuilder();

$repository = new Repository( CustomerEntity::class );
$repository->where( fn( 
  CustomerEntity $customer,
  DocumentEntity $document,
  DocumentItemEntity $documentItem,
  ProductEntity $product,
  ProductGroupEntity $productGroup
) => (  
  $customer->Name === 'LINDSON%DOUGLAS%DO%SANTOS%' &&
  $customer->Id === $document->CustomerId &&
  $document->State === [ DocumentState::Finalizado, DocumentState::Cancelado ] &&
  $document->CreatedAt >= '06/04/2023' &&
  $document->CreatedAt <= '10/04/2023' &&
  $document->Id === $documentItem->DocumentId &&
  $documentItem->ProductId === $product->Id &&
  $product->ProductGroupId === $productGroup->Id
));

$repository->orderByAsc( fn(CustomerEntity $customer) => $customer->CreatedAt );
$repository->queryBuilder();

// $repository = new Repository( ProductEntity::class );
// $repository->where( fn(
//   ProductEntity $product,
//   ProductGroupEntity $productGroup
// ) => $product->ProductGroupId === $productGroup->Id && $productGroup->Name === 'Salão' );

// $repository->queryBuilder();

// echo $repository->sql;

$query = DB::queryWithPrepared( $repository->sql, $repository->prepareds);
print_r( $query );



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