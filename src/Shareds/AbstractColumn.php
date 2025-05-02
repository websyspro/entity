<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnType;

class AbstractColumn
{
  public AttributeType $attributeType = AttributeType::Column;
  public ColumnType $columnType = ColumnType::Date;
}