<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableEventUpdates
extends StructureTableAbstract
{
  public function List(
  ): DataList {
    return $this->Properties(
      AttributeType::Update
    );
  }

  public function ListNames(
  ): array {
    return $this->List()->Mapper(
      fn(IProperties $properties) => (
        $properties->name
      )
    )->All();
  }  
}