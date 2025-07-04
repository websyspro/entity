<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\Shareds\ForeignKeyItem;
use Websyspro\Entity\Core\Shareds\ForeignKeyReferenceItem;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableForeignKeys
extends StructureTableAbstract
{
  public function list(
  ): DataList {
    return $this->properties(
      AttributeType::foreigns
    );
  }

  public function listNames(
    string $table
  ): DataList {
    return (
      $this->list()->mapper(
        fn(IProperties $property) => (
          new ForeignKeyItem(
            $table, $property->name, new ForeignKeyReferenceItem(
              $property->items->first()->referenceClass
            )
          )
        )
      )
    );
  }

  public function isForeignKey(
    string $table,
    string $name
  ): bool {
    return $this->listNames($table)->where(
      fn(ForeignKeyItem $foreignKeyItem) => (
        $foreignKeyItem->name === $name
      )
    )->exist();
  } 
}