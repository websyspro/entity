<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableEventDeletes
extends StructureTableAbstract
{
  public function List(
  ): DataList {
    return $this->Properties(
      AttributeType::Delete
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