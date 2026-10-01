<?php

namespace Websyspro\Entity;

use Websyspro\Entity\BaseEntity;
use Websyspro\Entity\Decorators\InitialDefault;
use Websyspro\Entity\Decorators\Nullable;
use Websyspro\Entity\Decorators\PrimaryKey;
use Websyspro\Entity\Decorators\Required;
use Websyspro\Entity\Types\ColumnAutoUUID;
use Websyspro\Entity\Types\ColumnDatetime;
use Websyspro\Entity\Types\ColumnFlag;
use Websyspro\Entity\Types\ColumnUUID;

abstract class BaseUUIDEntity 
extends BaseEntity
{
    #[PrimaryKey()]
    #[Required()]
    public ColumnAutoUUID $Id;

    public ColumnFlag $IsActive;

    public ColumnFlag $IsDeleted;

    #[InitialDefault(AutoDatetime::class)]
    public ColumnDatetime $Created;

    #[Nullable()]
    public ColumnUUID $CreatedById;

    #[Nullable]
    #[InitialDefault(AutoDatetime::class)]
    public ColumnDatetime $Updated;

    #[Nullable]
    public ColumnUUID $UpdatedById;

    #[Nullable]
    public ColumnDatetime $Deleted;

    #[Nullable]
    public ColumnUUID $DeletedById;
}
