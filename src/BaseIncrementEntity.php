<?php

namespace Websyspro\Entity;

use Websyspro\Entity\Decorators\InitialDefault;
use Websyspro\Entity\Decorators\Nullable;
use Websyspro\Entity\Decorators\PrimaryKey;
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

    public ColumnFlag $isActive;

    public ColumnFlag $isDeleted;

    #[InitialDefault(AutoDatetime::class)]
    public ColumnDatetime $created;

    #[Nullable()]
    public ColumnInt $createdById;

    #[Nullable]
    #[InitialDefault(AutoDatetime::class)]
    public ColumnDatetime $updated;

    #[Nullable]
    public ColumnInt $updatedById;

    #[Nullable]
    public ColumnDatetime $deleted;

    #[Nullable]
    public ColumnInt $deletedById;
}
