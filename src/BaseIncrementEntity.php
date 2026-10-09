<?php

namespace Websyspro\Entity;

use Websyspro\Entity\Decorators\InitialDeleteDefault;
use Websyspro\Entity\Decorators\InitialInsertDefault;
use Websyspro\Entity\Decorators\InitialUpdateDefault;
use Websyspro\Entity\Decorators\Utils\AutoDatetime;
use Websyspro\Entity\Decorators\PrimaryKey;
use Websyspro\Entity\Decorators\Nullable;
use Websyspro\Entity\Decorators\Required;
use Websyspro\Entity\Types\ColumnAutoIncrement;
use Websyspro\Entity\Types\ColumnDatetime;
use Websyspro\Entity\Types\ColumnFlag;
use Websyspro\Entity\Types\ColumnInt;

abstract class BaseIncrementEntity 
extends BaseEntity
{
  #[PrimaryKey()]
  #[Required()]
  public ColumnAutoIncrement $id;

  #[InitialInsertDefault(1)]
  public ColumnFlag $isActive;

  #[InitialInsertDefault(0)]
  public ColumnFlag $isDeleted;

  #[InitialInsertDefault(AutoDatetime::class)]
  public ColumnDatetime $created;

  #[Nullable()]
  public ColumnInt $createdById;

  #[Nullable]
  #[InitialUpdateDefault(AutoDatetime::class)]
  public ColumnDatetime $updated;

  #[Nullable]
  public ColumnInt $updatedById;

  #[Nullable]
  #[InitialDeleteDefault(AutoDatetime::class)]
  public ColumnDatetime $deleted;

  #[Nullable]
  public ColumnInt $deletedById;
}
