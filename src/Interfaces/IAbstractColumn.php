<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnType;

class IAbstractColumn
{
  public AttributeType $attributeType = AttributeType::column;
  public ColumnType $columnType = ColumnType::date;

  public function sql(
  ): string {
    return "";
  }
}