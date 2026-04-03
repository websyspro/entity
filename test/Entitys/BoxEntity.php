<?php 

namespace Websyspro\Test\Entitys;

use Websyspro\Entity\Decorations\BaseEntity;
use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\Statistics\Index;
use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Decimal;
use Websyspro\Entity\Decorations\Columns\Number;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Shareds\EntityList;

#[EntityName( "boxTest" )]
class BoxEntity 
extends BaseEntity
{
  #[Text(32)]
  #[Index()]
  #[Unique()]
  public string $Name;

  #[Text(1)]
  #[Unique()]
  public string $State;

  #[Number()]
  #[ForeignKey(OperatorEntity::class)]
  #[Unique(2)]
  public string $OperatorId;
  public OperatorEntity $Operador;

  #[Text(255)]
  public string $Printer;

  #[Datetime()]
  public string $OpeningAt;

  #[Decimal(10,2)]
  public string $OpeningBalance;

  public EntityList $Documents;
}