<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\Shareds\OneToManyItem;
use Websyspro\Entity\Core\Shareds\OneToManyReferenceItem;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableOneToManys
extends StructureTableAbstract
{
  public function list(
  ): DataList {
    return $this->properties(
      AttributeType::oneToMany
    );
  }

  public function listNames(
    string $table
  ): DataList {
    return (
      $this->list()->mapper(
        fn(IProperties $property) => (
          new OneToManyItem(
            $table, $property->name, new OneToManyReferenceItem(
              $property->items->first()->referenceClass
            )
          )
        )
      )
    );
  }

  public function isOneToMany(
    string $table,
    string $name
  ): bool {
    return $this->listNames($table)->where(
      fn(OneToManyItem $oneToOneItem) => (
        $oneToOneItem->name === $name
      )
    )->exist();
  } 
}