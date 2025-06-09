<?php

use Websyspro\Entity\Core\StructureDatabase;
use Websyspro\Entity\Repository;
use Websyspro\Entity\Test\Shops\Entitys\BoxEntity;
use Websyspro\Entity\Test\Shops\Imports\ProductGroupImport;
use Websyspro\Entity\Test\Shops\ShopDatabase;

// $structureDatabase = (
//   new StructureDatabase(
//     ShopDatabase::class
//   )
// );
// $structureDatabase->Update();

Repository::Entity(BoxEntity::class)->Insert(ProductGroupImport::rows());