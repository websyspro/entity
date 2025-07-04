<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableEventDeletes
extends StructureTableAbstract
{
  public function list(
  ): DataList {
    return $this->properties(
      AttributeType::delete
    );
  }

  public function listNames(
  ): array {
    return $this->list()->mapper(
      fn(IProperties $properties) => (
        $properties->name
      )
    )->all();
  } 
}