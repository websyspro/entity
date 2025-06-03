<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\TList;
use Websyspro\Entity\Enums\TAttributeType;

class TStructureTablePrimaryKeys
extends TStructureTableAbstract
{
  public function List(
  ): TList {
    return $this->Properties(
      TAttributeType::PrimaryKey
    );
  }
}