<?php

use Websyspro\Entity\Core\StructureTable;
use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Decimal;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Decorations\Columns\Number;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\Events\Delete;
use Websyspro\Entity\Decorations\Events\Insert;
use Websyspro\Entity\Decorations\Events\Update;
use Websyspro\Entity\Decorations\Generations\AutoIncrement;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Decorations\Statistics\Index;

require_once "./vendor/autoload.php";

class BaseEntity
{
  #[NotNull()]
  #[Number()]
  #[PrimaryKey()]
  #[AutoIncrement()]    
  public int $Id;

  #[Flag()]
  #[NotNull()]
  #[Insert(1)]
  public bool $Actived;

  #[NotNull()]
  #[Number()]
  #[Insert(1)]
  public int $ActivedBy;

  #[NotNull()]
  #[Datetime()]
  #[Insert(1)]
  public string $ActivedAt;

  #[NotNull()]
  #[Number()]
  #[Insert(1)] 
  public int $CreatedBy;

  #[NotNull()]
  #[Datetime()]
  #[Insert(1)]
  public string $CreatedAt;

  #[Number()]
  #[Update(1)]
  public int $UpdatedBy;

  #[Datetime()]
  #[Update(1)]
  public string $UpdatedAt;

  #[Flag()]
  #[Delete(1)]
  #[Insert(0)]
  public bool $Deleted;

  #[Number()]
  #[Delete(1)]
  public int $DeletedBy;

  #[Datetime()]
  #[Delete(1)]
  public string $DeletedAt;
}

class OperatorEntity
extends BaseEntity
{
  #[Text(64)]
  #[Unique(1)]
  public string $Name;
}

class BoxEntity 
extends BaseEntity
{
  #[Text(32)]
  #[Index()]
  #[Unique()]
  public string $Name;

  #[Text(1)]
  public string $State;

  #[Number()]
  #[ForeignKey(OperatorEntity::class)]
  public string $OperatorId;

  #[Text(255)]
  public string $Printer;

  #[Datetime()]
  public string $OpeningAt;

  #[Decimal(10,2)]
  public string $OpeningBalance;
}

$structure = new StructureTable(BoxEntity::class);

print_r($structure->Columns()->Types());