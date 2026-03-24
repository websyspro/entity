<?php

use Websyspro\Entity\Core\Database;
use Websyspro\Test\Crm\Entitys\ColecaoEntity;
use Websyspro\Test\Entitys\ProductGroupEntity;
use Websyspro\Test\Entitys\ProductEntity;
use Websyspro\Entity\Repository;

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

// $repository = new Repository( CustomerEntity::class );
// $repository->where( fn( 
//   CustomerEntity $customer,
//   DocumentEntity $document,
//   DocumentItemEntity $documentItem,
//   ProductEntity $product,
//   ProductGroupEntity $productGroup
// ) => (  
//   $customer->Name === 'LINDSON%DOUGLAS%DO%SANTOS%' &&
//   $customer->Id === $document->CustomerId &&
//   $document->State === [ DocumentState::Finalizado, DocumentState::Cancelado ] &&
//   $document->CreatedAt >= '06/04/2023' &&
//   $document->CreatedAt <= '10/04/2023' &&
//   $document->Id === $documentItem->DocumentId &&
//   $documentItem->ProductId === $product->Id &&
//   $product->ProductGroupId === $productGroup->Id
// ));

// //$repository->orderByAsc( fn(CustomerEntity $customer) => $customer->Id );
// $repository->orderByDesc( fn(CustomerEntity $customer) => $customer->Id );
// $repository->queryBuilder();

// $repository = new Repository( ProductEntity::class );
// $repository->where( fn( ProductEntity $product, ProductGroupEntity $productGroup ) => $product->ProductGroupId === $productGroup->Id );
// //$repository->orderByDesc( fn ( ProductEntity $product ) => $product->Id );
// $repository->paged( 1, 24 );
// $repository->queryBuilder();

// echo $repository->sql;

// $query = Database::queryWithPrepared( $repository->sql, $repository->prepareds);
// print_r( $query );

$repository = new Repository( ColecaoEntity::class );
$repository->where( fn( ColecaoEntity $colecao ) => $colecao->IsActive === true );
$repository->orderByAsc( fn ( ColecaoEntity $colecao ) => $colecao->Nome );
$repository->paged( 1, 4 );
$repository->queryBuilder();

echo $repository->sql;

$query = Database::query( $repository->sql, $repository->prepareds->toArray());
print_r( $query );

// $query = Database::query( "select * from Colecao where Id=?", [ "E842AB51-21B2-491B-A0B6-012F1171478C" ]);
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