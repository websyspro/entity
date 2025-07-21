<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\Shareds\OneToOneItem;
use Websyspro\Entity\Core\Shareds\OneToOneReferenceItem;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableOneToOnes
extends StructureTableAbstract
{
  public function list(
  ): DataList {
    return $this->properties(
      AttributeType::oneToOne
    );
  }

  public function listNames(
    string $table
  ): DataList {
    return (
      $this->list()->mapper(
        fn(IProperties $property) => (
          new OneToOneItem(
            $table, $property->name, new OneToOneReferenceItem(
              $property->items->first()->referenceClass
            )
          )
        )
      )
    );
  }

  public function isOneToOne(
    string $table,
    string $name
  ): bool {
    return $this->listNames($table)->where(
      fn(OneToOneItem $oneToOneItem) => (
        $oneToOneItem->name === $name
      )
    )->exist();
  } 
}