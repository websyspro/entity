<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableGenerations
extends StructureTableAbstract
{
  public function list(
  ): DataList {
    return $this->properties(
      AttributeType::generations
    );
  }

  public function listNames(
  ): DataList  {
    return $this->list()->mapper(
      fn(IProperties $property) => (
        $property->name
      )
    );
  }
}