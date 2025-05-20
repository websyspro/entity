<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\Collection;
use Websyspro\Entity\Enums\AttributeType;

class StructureTableGenerations
extends StructureTableAbstract
{
  public function List(
  ): Collection {
    return $this->Properties(
      AttributeType::Generations
    );
  }
}