<?php

namespace Websyspro\Entity;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\StructureTable;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class Repository
{
  private StructureTable $structureTable;

  public function __construct(
    private string $table
  ){
    $this->structureTable = (
      new StructureTable(
        $this->table
      )
    );
  }

  public static function Entity(
    string $entity
  ): Repository {
    return new Repository($entity);
  }

  private function ListKeysNames(
  ): DataList {
    return $this->structureTable->Requireds()->ListKeysNames();
  }

  private function Columns(
  ): DataList {
    return $this->structureTable->Columns()->List()->Reduce(
      [], function(array $curr, IProperties $event){
        $curr[$event->name] = $event->items->First()->columnType;
        return $curr;
      }
    );
  }

  private function DefaultEvents(
    AttributeType $attributeType
  ): DataList {
    if(AttributeType::Insert === $attributeType){
      $defaultEvents = $this->structureTable->EventInserts()->List();
    } else
    if(AttributeType::Update === $attributeType){
      $defaultEvents = $this->structureTable->EventUpdates()->List(); 
    } else
    if(AttributeType::Delete === $attributeType){
      $defaultEvents = $this->structureTable->EventDeletes()->List();
    } 
    
    if($defaultEvents->Exist() === false){
      return DataList::Create();
    }

    $defaultEvents->Reduce([], function(array $curr, IProperties $event){
      $curr[$event->name] = $event->items->First()->Get();
      return $curr;
    });

    return $defaultEvents;
  }

  private function ParseDecode(
    DataList $row,
    DataList $columns
  ): DataList {
    $parseDecode = (
      $row->Mapper(
        fn(mixed $value, string $name) => (
          $columns->Copy()->WhereByKey(
            fn(string $columnName) => $columnName === $name
          )->First()->Encode($value)
        )
      )
    );

    return $parseDecode;
  }

  private function ParseDefaults(
    array $row,
    AttributeType $attributeType
  ): DataList {
    return DataList::Create(
      array_merge(
        $this->ListKeysNames()->All(), 
        $this->DefaultEvents($attributeType)->All(), $row
      )
    );
  }

  public function Insert(
    array $dataList = []
  ): bool {
    if(sizeof($dataList) === 0){
      return false;
    }

    $dataList = DataList::Create($dataList);
    $columnsList = $this->Columns();
    
    $dataList = $dataList->Mapper(
      fn(array $row) => (
        $this->ParseDecode(
          $this->ParseDefaults(
            $row, AttributeType::Insert
          ), $columnsList
        )->All()
      )
    );

    print_r($dataList);

    return true;
  } 
}