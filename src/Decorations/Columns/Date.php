<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnType;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Date
{
  public AttributeType $attributeType = AttributeType::Column;
  public ColumnType $columnType = ColumnType::Date;

  public function sql(
  ): object {
    return (object)[
      "type" => "date",
      "args" => ""
    ];
  }
}