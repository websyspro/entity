<?php

use Websyspro\Entity\Repository;
use Websyspro\Entity\Test\Shops\Entitys\DocumentEntity;

$documentRepo = new Repository(
  DocumentEntity::class
);

$documentRepo->where(
  fn(DocumentEntity $d) => (
    $d->Id === 1
  )
);

print_r($documentRepo->all());