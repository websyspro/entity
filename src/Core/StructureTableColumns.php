<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\TList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;
use Websyspro\Entity\Shareds\ColumnType;
use Websyspro\Entity\Shareds\Properties;

class StructureTableColumns
extends StructureTableAbstract
{
  public function List(
  ): TList  {
    return $this->Properties(
      AttributeType::Column
    );
  }

  public function ListType(
  ): TList {
    return (
      $this->List()->Mapper(
        fn( Properties $properties ) => (
          new ColumnType(
            $properties->name,
            $properties->items->Mapper(
              fn(AbstractColumn $abstractColumn) => (
                $abstractColumn->sql()
              )
            )->First()
          )
        )
      )
    );
  }

  public function ColumnExist(
    string $name
  ): bool {
    return (
      $this->ListType()->Find(
        fn(ColumnType $properties) => (
          $properties->name === $name
        )
      )->Count() !== 0
    );
  }
}