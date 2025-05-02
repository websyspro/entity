<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnType;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Flag
{
  public AttributeType $attributeType = AttributeType::Column;
  public ColumnType $columnType = ColumnType::Flag;

  public function sql(
  ): object {
    return (object)[
      "base" => $this->columnType,
      "type" => "flag",
      "args" => ""
    ];
  } 
}