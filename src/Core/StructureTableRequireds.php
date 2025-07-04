<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableRequireds
extends StructureTableAbstract
{
  public function list(
  ): DataList {
    return $this->properties(
      AttributeType::requireds
    );
  }

  public function listKeysNames(
  ): DataList {
    return (
      DataList::create(
        array_flip(
          $this->list()->mapper(
            fn(IProperties $properties) => (
              $properties->name
            )
          )->all()
        )
      )->mapper(fn() => null)
    );
  }  

  public function isRequired(
    string $name
  ): bool {
    return $this->list()->where(
      fn(IProperties $properties) => (
        $properties->name === $name
      )
    )->exist();
  } 
  
  public function sql(
    string $name
  ): string {
    return $this->isRequired($name)
      ? "Not Null" : "Null";
  }
}