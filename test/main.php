<?php

use Websyspro\Entity\Repository;
use Websyspro\Test\Entitys\DocumentItemEntity;
use Websyspro\SqlFromClass\StructureTokens;
use Websyspro\Test\Entitys\DocumentEntity;
use Websyspro\Test\Entitys\OperatorEntity;
use Websyspro\Test\Enums\DocumentState;
use Websyspro\Test\Entitys\BoxEntity;
use Websyspro\SqlFromClass\Shareds;

function Repository(
  int $boxId
): StructureTokens {
  return Shareds::createStructure(
    fn(
      BoxEntity $box,
      OperatorEntity $operator,
      DocumentEntity $document,
      DocumentItemEntity $documentItem,
    ) => (
      $box->Id === $boxId &&
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
    )
  );
}

$repository = new Repository( 
  BoxEntity::class
);

$repository->where( fn(
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
  )
);
$repository->getSql();

print_r($repository);
