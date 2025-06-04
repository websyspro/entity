<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnType;

class IAbstractColumn
{
  public AttributeType $attributeType = AttributeType::Column;
  public ColumnType $columnType = ColumnType::Date;

  public function sql(
  ): string {
    return "";
  }
}