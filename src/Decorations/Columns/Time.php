<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnType;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Time
{
  public AttributeType $attributeType = AttributeType::Column;
  public ColumnType $columnType = ColumnType::Time;

  public function sql(
  ): object {
    return (object)[
      "type" => "time",
      "args" => ""
    ];
  }
}