<?php

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\StructureDatabase;
use Websyspro\Entity\Test\Shops\Entitys\BoxEntity;
use Websyspro\Entity\Test\Shops\Entitys\CashMovementEntity;
use Websyspro\Entity\Test\Shops\Entitys\ConfigEntity;
use Websyspro\Entity\Test\Shops\Entitys\CustomerEntity;
use Websyspro\Entity\Test\Shops\Entitys\DocumentEntity;
use Websyspro\Entity\Test\Shops\Entitys\DocumentItemEntity;
use Websyspro\Entity\Test\Shops\Entitys\OperatorEntity;
use Websyspro\Entity\Test\Shops\Entitys\ProductEntity;
use Websyspro\Entity\Test\Shops\Entitys\ProductGroupEntity;
use Websyspro\Entity\Test\Shops\ShopDatabase;

$structureDatabase = (
  new StructureDatabase(
    DataList::Create([
      OperatorEntity::class,
      CustomerEntity::class,
      ProductGroupEntity::class,
      ProductEntity::class,
      BoxEntity::class,
      ConfigEntity::class,
      DocumentEntity::class,
      DocumentItemEntity::class,
      CashMovementEntity::class
    ]), ShopDatabase::class
  )
);
$structureDatabase->Update();

// Repository::Entity(OperatorEntity::class)->Insert(OperatorImport::rows());
// Repository::Entity(CustomerEntity::class)->Insert(CustumerImport::rows());
// Repository::Entity(ProductGroupEntity::class)->Insert(ProductGroupImport::rows());
// Repository::Entity(ProductEntity::class)->Insert(ProductImport::rows());
// Repository::Entity(BoxEntity::class)->Insert(BoxImport::rows());
// Repository::Entity(ConfigEntity::class)->Insert(ConfigImport::rows());
// Repository::Entity(DocumentEntity::class)->Insert(DocumentImport::rows());
// Repository::Entity(DocumentItemEntity::class)->Insert(DocumentItemImport::rows());
// Repository::Entity(CashMovementEntity::class)->Insert(CashMovementImport::rows());