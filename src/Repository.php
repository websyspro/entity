<?php

namespace Websyspro\Entity;

use Websyspro\Commons\DataList;
use Websyspro\Commons\Util;
use Websyspro\Database\Connect;
use Websyspro\DynamicSql\QueryBuild;
use Websyspro\Entity\Core\StructureTable;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;

class Repository
{
  public StructureTable $structureTable;

  public mixed $selectFn;
  public mixed $whereFn;
  public mixed $groupByFn;
  public mixed $orderByAscFn;
  public mixed $orderByDescFn;

  public function __construct(
    public string $table
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
    $module = (
      strtolower(
        Util::ClassName(
          $this->structureTable->module
        )
      )
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

  private function ParseEncode(
    DataList $row,
    DataList $columns
  ): DataList {
    $parseEncode = (
      $row->Mapper(
        fn(mixed $value, string $name) => (
          $columns->Copy()->WhereByKey(
            fn(string $columnName) => $columnName === $name
          )->First()->Encode($value)
        )
      )
    );

    return $parseEncode;
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
          )->First()->Decode($value)
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
        $this->Connect()->Exec(
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
            $this->ParseEncode(
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
  ): int {
    return $this->Connect()->Query(
      "Select Count(*) as CountRows From {$this->structureTable->table}"
    )->First()->CountRows;
  }

  public function QueryBuild(
    string $sql    
  ): DataList {
    $columns = (
      $this->Columns()
    );    

    return (
      $this->Connect()
        ->Query($sql)
        ->Mapper(fn(object $row) => (
          $this->ParseDecode(
            DataList::Create(
              (array)$row
            ), $columns
          )->First()
        ))
    );
  }

  public function SetProperty(
    string $key,
    mixed $value
  ): Repository {
    $this->{$key} = $value;
    return $this;
  }

  public function Select(
    callable $selectFn
  ): Repository {
    return $this->SetProperty(
      "selectFn", $selectFn
    );    
  }

  public function Where(
    callable $whereFn
  ): Repository {
    return $this->SetProperty(
      "whereFn", $whereFn
    );
  }

  public function GroupBy(
    callable $groupByFn
  ): Repository {
    return $this->SetProperty(
      "groupByFn", $groupByFn
    );
  }

  public function orderByAsc(
    callable $orderByAscFn
  ): Repository {
    return $this->SetProperty(
      "orderByAscFn", $orderByAscFn
    );
  }  

  public function orderByDesc(
    callable $orderByDescFn
  ): Repository {
    return $this->SetProperty(
      "orderByDescFn", $orderByDescFn
    );
  }

  public function All(
  ): DataList {
    $queryBuild = (
      new QueryBuild(
        $this->table
      )
    );

    if(isset($this->selectFn))
      $queryBuild->Select($this->selectFn);
    if(isset($this->whereFn))
      $queryBuild->Where($this->whereFn);
    if(isset($this->groupByFn))
      $queryBuild->GroupBy($this->groupByFn);
    if(isset($this->orderByAscFn))
      $queryBuild->OrderByAsc($this->orderByAscFn);
    if(isset($this->orderByDescFn))
      $queryBuild->OrderByDesc($this->orderByDescFn);

    return $this->QueryBuild(
      $queryBuild->Get()
    );
  }
}