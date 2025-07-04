<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Date
extends IAbstractColumn
{
  public AttributeType $attributeType = AttributeType::column;
  public ColumnType $columnType = ColumnType::date;

  public function sql(
  ): string {
    return "date";
  }
}