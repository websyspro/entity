<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnOrder;
use Websyspro\Entity\Interfaces\IAbstractColumn;
use Websyspro\Entity\Interfaces\IColumnType;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableColumns
extends StructureTableAbstract
{
  public function list(
  ): DataList  {
    $propertiesInitial = $this->properties(
      AttributeType::column
    )->where(fn(IProperties $property) => (
      in_array( $property->name, explode(
        "|", ColumnOrder::initial->value
      )) === true
    ));

    $propertiesBase = $this->properties(
      AttributeType::column
    )->where(fn(IProperties $property) => (
      in_array( $property->name, explode(
        "|", ColumnOrder::base->value
      )) === false
    ));
    
    $propertiesEnd = $this->properties(
      AttributeType::column
    )->where(fn(IProperties $property) => (
      in_array( $property->name, explode(
        "|", ColumnOrder::end->value
      )) === true
    ));    

    return DataList::create(
      array_merge(
        $propertiesInitial->all(),
        $propertiesBase->all(),
        $propertiesEnd->all()
      )
    );
  }

  public function listType(
  ): DataList {
    return (
      $this->list()->mapper(
        fn(IProperties $properties) => (
          new IColumnType(
            $properties->name,
            $properties->items->mapper(
              fn(IAbstractColumn $abstractColumn) => (
                $abstractColumn->sql()
              )
            )->first()
          )
        )
      )
    );
  }

  public function columnExist(
    string $name
  ): bool {
    return (
      $this->listType()->where(
        fn(IColumnType $properties) => (
          $properties->name === $name
        )
      )->exist()
    );
  }

  public function type(
    string $name
  ): string {
    return (
      $this->listType()->where(
        fn(IColumnType $properties) => (
          $properties->name === $name
        )
      )->first()->type
    );
  } 
  
  public function listNames(
  ): DataList  {
    return $this->list()->mapper(
      fn(IProperties $property) => (
        $property->name
      )
    );
  }  

  public function before(
    string $name
  ): string {
    $columnBefore = $this->listType()->eq(
      $this->listType()->indexOf(
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