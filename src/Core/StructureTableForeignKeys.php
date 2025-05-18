<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\TList;
use Websyspro\Entity\Enums\AttributeType;

class StructureTableForeignKeys
extends StructureTableAbstract
{
  public function List(
  ): TList {
    return $this->Properties(
      AttributeType::Foreigns
    );
  }
}