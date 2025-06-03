<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\TList;
use Websyspro\Entity\Enums\TAttributeType;

class TStructureTableGenerations
extends TStructureTableAbstract
{
  public function List(
  ): TList {
    return $this->Properties(
      TAttributeType::Generations
    );
  }
}