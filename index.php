<?php

use Websyspro\Entity\Core\TStructureDatabase;
use Websyspro\Entity\Decorations\Columns\TDatetime;
use Websyspro\Entity\Decorations\Columns\TDecimal;
use Websyspro\Entity\Decorations\Columns\TFlag;
use Websyspro\Entity\Decorations\Columns\TNumber;
use Websyspro\Entity\Decorations\Columns\TText;
use Websyspro\Entity\Decorations\Constraints\TForeignKey;
use Websyspro\Entity\Decorations\Constraints\TPrimaryKey;
use Websyspro\Entity\Decorations\Constraints\TUnique;
use Websyspro\Entity\Decorations\TEntityList;
use Websyspro\Entity\Decorations\Events\TDelete;
use Websyspro\Entity\Decorations\Events\TInsert;
use Websyspro\Entity\Decorations\Events\TUpdate;
use Websyspro\Entity\Decorations\Generations\TAutoIncrement;
use Websyspro\Entity\Decorations\Requireds\TNotNull;
use Websyspro\Entity\Decorations\Statistics\Index;

require_once "./vendor/autoload.php";

class BaseEntity
{
  #[TNotNull()]
  #[TNumber()]
  #[TPrimaryKey()]
  #[TAutoIncrement()]    
  public int $Id;

  #[TFlag()]
  #[TNotNull()]
  #[TInsert(1)]
  public bool $Actived;

  #[TNotNull()]
  #[TNumber()]
  #[TInsert(1)]
  public int $ActivedBy;

  #[TNotNull()]
  #[TDatetime()]
  #[TInsert(1)]
  public string $ActivedAt;

  #[TNotNull()]
  #[TNumber()]
  #[TInsert(1)] 
  public int $CreatedBy;

  #[TNotNull()]
  #[TDatetime()]
  #[TInsert(1)]
  public string $CreatedAt;

  #[TNumber()]
  #[TUpdate(1)]
  public int $UpdatedBy;

  #[TDatetime()]
  #[TUpdate(1)]
  public string $UpdatedAt;

  #[TFlag()]
  #[TDelete(1)]
  #[TInsert(0)]
  public bool $Deleted;

  #[TNumber()]
  #[TDelete(1)]
  public int $DeletedBy;

  #[TDatetime()]
  #[TDelete(1)]
  public string $DeletedAt;
}

class OperatorEntity
extends BaseEntity
{
  #[TText(64)]
  #[TUnique(1)]
  public string $Name;
}

class BoxEntity 
extends BaseEntity
{
  #[TText(32)]
  #[Index()]
  #[TUnique()]
  public string $Name;

  #[TText(1)]
  public string $State;

  #[TNumber()]
  #[TForeignKey(OperatorEntity::class)]
  public string $OperatorId;

  #[TText(255)]
  public string $Printer;

  #[TDatetime()]
  public string $OpeningAt;

  #[TDecimal(10,2)]
  public string $OpeningBalance;
}

// $structure = (
//   new StructureTable(
//     BoxEntity::class
//   )
// )->Requireds()->List();

// print_r($structure);

#[TEntityList([
  BoxEntity::class,
  OperatorEntity::class
])]
class ShopDatabase {}

$structureDatabase = new TStructureDatabase(ShopDatabase::class);
$structureDatabase->Update();
