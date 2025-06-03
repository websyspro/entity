<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Enums\TColumnType;

class TAbstractColumn
{
  public TAttributeType $attributeType = TAttributeType::Column;
  public TColumnType $columnType = TColumnType::Date;

  public function sql(
  ): string {
    return "";
  }
}