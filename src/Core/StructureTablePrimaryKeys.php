<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\TList;
use Websyspro\Entity\Enums\AttributeType;

class StructureTablePrimaryKeys
extends StructureTableAbstract
{
  public function List(
  ): TList {
    return $this->Properties(
      AttributeType::PrimaryKey
    );
  }
}