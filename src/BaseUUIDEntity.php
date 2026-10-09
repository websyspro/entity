<?php

namespace Websyspro\Entity;

use Websyspro\Entity\BaseEntity;
use Websyspro\Entity\Decorators\InitialDeleteDefault;
use Websyspro\Entity\Decorators\InitialInsertDefault;
use Websyspro\Entity\Decorators\InitialUpdateDefault;
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
  #[InitialInsertDefault(AutoGuid::class)]
  public ColumnAutoUUID $id;

  #[InitialInsertDefault(1)]
  public ColumnFlag $isActive;

  #[InitialInsertDefault(0)]
  public ColumnFlag $isDeleted;

  #[InitialInsertDefault(AutoDatetime::class)]
  public ColumnDatetime $created;

  #[Nullable()]
  public ColumnUUID $createdById;

  #[Nullable]
  #[InitialUpdateDefault(AutoDatetime::class)]
  public ColumnDatetime $updated;

  #[Nullable]
  public ColumnUUID $updatedById;

  #[Nullable]
  #[InitialDeleteDefault(AutoDatetime::class)]  
  public ColumnDatetime $deleted;

  #[Nullable]
  public ColumnUUID $deletedById;
}
