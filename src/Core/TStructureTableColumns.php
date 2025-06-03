<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\TList;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;
use Websyspro\Entity\Shareds\TColumnType;
use Websyspro\Entity\Shareds\TProperties;

class TStructureTableColumns
extends TStructureTableAbstract
{
  public function List(
  ): TList  {
    return $this->Properties(
      TAttributeType::Column
    );
  }

  public function ListType(
  ): TList {
    return (
      $this->List()->Mapper(
        fn(TProperties $properties) => (
          new TColumnType(
            $properties->name,
            $properties->items->Mapper(
              fn(TAbstractColumn $abstractColumn) => (
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
      $this->ListType()->Where(
        fn(TColumnType $properties) => (
          $properties->name === $name
        )
      )->Count() !== 0
    );
  }
}