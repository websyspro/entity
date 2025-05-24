<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\Collection;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;
use Websyspro\Entity\Shareds\ColumnType;
use Websyspro\Entity\Shareds\Properties;

class StructureTableColumns
extends StructureTableAbstract
{
  public function List(
  ): Collection  {
    return $this->Properties(
      AttributeType::Column
    );
  }

  public function ListType(
  ): Collection {
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
      $this->ListType()->Where(
        fn(ColumnType $properties) => (
          $properties->name === $name
        )
      )->Count() !== 0
    );
  }
}