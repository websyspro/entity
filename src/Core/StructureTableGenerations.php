<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableGenerations
extends StructureTableAbstract
{
  public function List(
  ): DataList {
    return $this->Properties(
      AttributeType::Generations
    );
  }

  public function ListNames(
  ): DataList  {
    return $this->List()->Mapper(
      fn(IProperties $property) => (
        $property->name
      )
    );
  }
}