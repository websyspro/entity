<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Text
extends IAbstractColumn
{
  public AttributeType $attributeType = AttributeType::column;
  public ColumnType $columnType = ColumnType::text;

  public function __construct(
    public readonly int $size = 255
  ){}

  public function sql(
  ): string {
    return sprintf("varchar(%s)", ...[
      $this->size
    ]);
  } 
}