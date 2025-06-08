<?php

use Websyspro\Entity\Core\StructureDatabase;
use Websyspro\Entity\Test\Shops\ShopDatabase;

$structureDatabase = (
  new StructureDatabase(
    ShopDatabase::class
  )
);
$structureDatabase->Update();