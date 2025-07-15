<?php

use Websyspro\Entity\Repository;
use Websyspro\Entity\Test\Shops\Entitys\BoxEntity;
use Websyspro\Entity\Test\Shops\Entitys\CustomerEntity;
use Websyspro\Entity\Test\Shops\Entitys\DocumentEntity;
use Websyspro\Entity\Test\Shops\Entitys\DocumentItemEntity;
use Websyspro\Entity\Test\Shops\Entitys\OperatorEntity;
use Websyspro\Entity\Test\Shops\Entitys\ProductEntity;
use Websyspro\Entity\Test\Shops\Entitys\ProductGroupEntity;

$documentRepo = new Repository(
  DocumentEntity::class
);

$documentRepo
  ->where(
    fn(DocumentEntity $d, DocumentItemEntity $i, ProductEntity $p, CustomerEntity $c, OperatorEntity $o, BoxEntity $b, ProductGroupEntity $g) => (
      $d->Id == [12] && $i->DocumentId == $d->Id && $i->ProductId == $p->Id && $d->CustomerId == $c->Id && $d->OperatorId == $o->Id && $d->BoxId == $b->Id && $p->ProductGroupId == $g->Id
    ))
  ->one();