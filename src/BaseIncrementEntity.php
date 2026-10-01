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
    public ColumnAutoIncrement $Id;

    public ColumnFlag $IsActive;

    public ColumnFlag $IsDeleted;

    #[InitialDefault(AutoDatetime::class)]
    public ColumnDatetime $Created;

    #[Nullable()]
    public ColumnInt $CreatedById;

    #[Nullable]
    #[InitialDefault(AutoDatetime::class)]
    public ColumnDatetime $Updated;

    #[Nullable]
    public ColumnInt $UpdatedById;

    #[Nullable]
    public ColumnDatetime $Deleted;

    #[Nullable]
    public ColumnInt $DeletedById;
}
