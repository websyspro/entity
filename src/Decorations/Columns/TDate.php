<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\TColumnType;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class TDate extends TAbstractColumn
{
  public TAttributeType $attributeType = TAttributeType::Column;
  public TColumnType $columnType = TColumnType::Date;

  public function sql(
  ): string {
    return "date";
  }
}