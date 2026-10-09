<?php

namespace Websyspro\Entity;

use Websyspro\Entity\BaseEntity;
use Websyspro\Entity\Decorators\InitialDefault;
use Websyspro\Entity\Decorators\Nullable;
use Websyspro\Entity\Decorators\PrimaryKey;
use Websyspro\Entity\Decorators\Required;
use Websyspro\Entity\Decorators\Utils\AutoGuid;
use Websyspro\Entity\Types\ColumnAutoUUID;
use Websyspro\Entity\Types\ColumnDatetime;
use Websyspro\Entity\Types\ColumnFlag;
use Websyspro\Entity\Types\ColumnUUID;

abstract class BaseUUIDEntity 
extends BaseEntity
{
  #[Required()]
  #[PrimaryKey()]
  #[InitialDefault(AutoGuid::class)]
  public ColumnAutoUUID $id;

  #[InitialDefault(1)]
  public ColumnFlag $isActive;

  #[InitialDefault(0)]
  public ColumnFlag $isDeleted;

  #[InitialDefault(AutoDatetime::class)]
  public ColumnDatetime $created;

  #[Nullable()]
  public ColumnUUID $createdById;

  #[Nullable]
  #[InitialDefault(AutoDatetime::class)]
  public ColumnDatetime $updated;

  #[Nullable]
  public ColumnUUID $updatedById;

  #[Nullable]
  public ColumnDatetime $deleted;

  #[Nullable]
  public ColumnUUID $deletedById;
}
