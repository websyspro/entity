<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Text
extends AbstractColumn
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