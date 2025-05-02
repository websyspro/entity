<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnType;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Text
{
  public AttributeType $attributeType = AttributeType::Column;
  public ColumnType $columnType = ColumnType::Text;

  public function __construct(
    public readonly int $size = 255
  ){}

  public function sql(
  ): object {
    return (object)[
      "base" => $this->columnType,
      "type" => "text",
      "args" => "{$this->size}"
    ];
  } 
}