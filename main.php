<?php

use Websyspro\Entity\Repository;
use Websyspro\Entity\Test\Shops\Entitys\BoxEntity;
use Websyspro\Entity\Test\Shops\Entitys\CustomerEntity;
use Websyspro\Entity\Test\Shops\Entitys\DocumentEntity;
use Websyspro\Entity\Test\Shops\Entitys\DocumentItemEntity;
use Websyspro\Entity\Test\Shops\Entitys\OperatorEntity;
use Websyspro\Entity\Test\Shops\Entitys\ProductEntity;
use Websyspro\Entity\Test\Shops\Entitys\ProductGroupEntity;

$repo = new Repository(
  DocumentEntity::class
);

$repo
  ->where(
    fn(DocumentEntity $d, DocumentItemEntity $i, CustomerEntity $c, BoxEntity $b, OperatorEntity $o, ProductEntity $p, ProductGroupEntity $g) => (
      $d->Id == [12, 14, 15, 17, 110, 456, 1012, 787, 898, 654] && $i->DocumentId == $d->Id && $c->Id == $d->CustomerId && $b->Id == $d->BoxId && $o->Id == $d->OperatorId && $p->Id == $i->ProductId && $g->Id == $p->ProductGroupId
    ))
  ->orderByAsc(
    fn(DocumentEntity $d) => [
      $d->Id
    ]
  )
  ->paged(1, 1)  
  ->one();