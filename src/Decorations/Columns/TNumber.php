<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\TColumnType;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class TNumber extends TAbstractColumn
{
  public TAttributeType $attributeType = TAttributeType::Column;
  public TColumnType $columnType = TColumnType::Number;

  public function sql(
  ): string {
    return "bigint";
  } 
}