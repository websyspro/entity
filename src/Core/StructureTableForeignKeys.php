<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableForeignKeys
extends StructureTableAbstract
{
  public function List(
  ): DataList {
    return $this->Properties(
      AttributeType::Foreigns
    );
  }
}