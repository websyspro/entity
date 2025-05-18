<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\TList;
use Websyspro\Entity\Enums\AttributeType;

class StructureTableStatistics
extends StructureTableAbstract
{
  public function List(
  ): TList {
    return $this->Properties(
      AttributeType::Indexes
    );
  }
}