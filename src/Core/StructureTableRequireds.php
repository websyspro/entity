<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableRequireds
extends StructureTableAbstract
{
  public function List(
  ): DataList {
    return $this->Properties(
      AttributeType::Requireds
    );
  }

  public function ListKeysNames(
  ): DataList {
    return (
      DataList::Create(
        array_flip(
          $this->List()->Mapper(
            fn(IProperties $properties) => (
              $properties->name
            )
          )->All()
        )
      )->Mapper(fn() => null)
    );
  }  

  public function IsRequired(
    string $name
  ): bool {
    return $this->List()->Where(
      fn(IProperties $properties) => (
        $properties->name === $name
      )
    )->Exist();
  } 
  
  public function Sql(
    string $name
  ): string {
    return $this->IsRequired($name)
      ? "Not Null" : "Null";
  }
}