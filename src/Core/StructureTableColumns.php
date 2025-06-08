<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IAbstractColumn;
use Websyspro\Entity\Interfaces\IColumnType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableColumns
extends StructureTableAbstract
{
  public function List(
  ): DataList  {
    return $this->Properties(
      AttributeType::Column
    );
  }

  public function ListType(
  ): DataList {
    return (
      $this->List()->Mapper(
        fn(IProperties $properties) => (
          new IColumnType(
            $properties->name,
            $properties->items->Mapper(
              fn(IAbstractColumn $abstractColumn) => (
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
        fn(IColumnType $properties) => (
          $properties->name === $name
        )
      )->Exist()
    );
  }

  public function Type(
    string $name
  ): string {
    return (
      $this->ListType()->Where(
        fn(IColumnType $properties) => (
          $properties->name === $name
        )
      )->First()->type
    );
  }  

  public function Before(
    string $name
  ): string {
    $columnBefore = $this->ListType()->Eq(
      $this->ListType()->IndexOf(
        fn(IColumnType $columnType) => (
          $columnType->name === $name
        )
      ) - 1
    );

    if($columnBefore === null){
      return "";
    }

    return "after {$columnBefore->name}";
  }
}