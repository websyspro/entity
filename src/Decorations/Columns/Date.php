<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Date extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::Column;
  public ColumnType $columnType = ColumnType::Date;

  public function sql(
  ): string {
    return "";
  }
}