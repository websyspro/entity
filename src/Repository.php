<?php

namespace Websyspro\Entity;

use Websyspro\Commons\DataList;
use Websyspro\Commons\Util;
use Websyspro\Database\Connect;
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
    return new static($entity);
  }

  public function Connect(
  ): Connect {
    $module = Util::ClassName(
      $this->structureTable->module
    );

    return Connect::Set($module);
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

  private function InsertValues(
    DataList $dataList
  ): bool {
    $dataHeaders = DataList::Create(
      array_keys($dataList->First())
    );

    $dataList->Chunk(500);
    $dataList->Mapper(
      fn(DataList $dataRows) => (
        $dataRows->Mapper(
          fn(array $row) => (
            sprintf("(%s)", ...[
              DataList::Create(
                $row
              )->JoinWithComma()
            ])
          )
        )
      )
    );

    $dataList->Mapper(fn(DataList $dataRows) => (
      sprintf("Insert Into {$this->structureTable->table} (%s) values %s", ...[
        $dataHeaders->JoinWithComma(),
        $dataRows->JoinWithComma()
      ])
    ));

    $dataList->Mapper(
      fn(string $insertScript) => (
        Connect::Set("shop")->Exec(
          $insertScript
        )
      )
    );

    return true;
  }

  public function Insert(
    array $dataList = []
  ): bool {
    if(sizeof($dataList) === 0){
      return false;
    }

    $columnsList = (
      $this->Columns()
    );
    
    return (
      $this->InsertValues(
        DataList::Create(
          $dataList
        )->Mapper(
          fn(array $row) => (
            $this->ParseDecode(
              $this->ParseDefaults(
                $row, AttributeType::Insert
              ), $columnsList
            )->All()
          )
        )
      )
    );
  }

  public function Count(
  ): DataList {
    return $this->Connect()->Query(
      "Select Count(*) as CountRows From {$this->structureTable->table}"
    );
  }
}