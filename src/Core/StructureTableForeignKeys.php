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
  public function List(
  ): DataList {
    return $this->Properties(
      AttributeType::Foreigns
    );
  }

  public function ListNames(
    string $table
  ): DataList {
    return (
      $this->List()->Mapper(
        fn(IProperties $property) => (
          new ForeignKeyItem(
            $table, $property->name, new ForeignKeyReferenceItem(
              $property->items->First()->referenceClass
            )
          )
        )
      )
    );
  }

  public function IsForeignKey(
    string $table,
    string $name
  ): bool {
    return $this->ListNames($table)->Where(
      fn(ForeignKeyItem $foreignKeyItem) => (
        $foreignKeyItem->name === $name
      )
    )->Exist();
  } 
}