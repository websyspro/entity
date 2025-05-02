<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnType;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Datetime
{
  public AttributeType $attributeType = AttributeType::Column;
  public ColumnType $columnType = ColumnType::Datetime;

  public function sql(
  ): object {
    return (object)[
      "base" => $this->columnType,
      "type" => "datetime",
      "args" => ""
    ];
  } 
}