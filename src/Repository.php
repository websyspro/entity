<?php

namespace Websyspro\Entity;

use Websyspro\Commons\DataList;
use Websyspro\Commons\Util;
use Websyspro\Database\Connect;
use Websyspro\DynamicSql\Core\DataByFn;
use Websyspro\DynamicSql\QueryBuild;
use Websyspro\Entity\Core\Shareds\StdClassToEntity;
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
  ): array {
    return (
      $this->structureTable->Columns()->List()->Reduce(
        [], function(array $curr, IProperties $event){
          $curr[$event->name] = $event->items->First()->columnType;
          return $curr;
        }
      )->All()
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
    array $row,
    array $columns
  ): array {
    return (
      Util::Mapper(
        $row, fn(mixed $value, string $key) => (
          $columns[$key]->Encode($value)
        )
      )
    );
  }

  private function ParseDecode(
    DataList $row,
    DataList $columns
  ): DataList {
    $row->Mapper(
      fn(mixed $stdClass) => (
        StdClassToEntity::Parse(
          $stdClass, $this->table
        )
      )
    );

    $row->Mapper(
      fn(mixed $stdClass) => (
        Util::Mapper(
          $stdClass, function(
            mixed $value, 
            string $name
          ) use($columns) {
            return $columns->Copy()->WhereByKey(
              fn(string $columnName) => $columnName === $name
            )->First()->Decode($value);
          }
        )
      )
    );

    return $row;
  }   

  private function ParseDefaults(
    array $row,
    AttributeType $attributeType
  ): array {
    return array_merge(
      $this->ListKeysNames()->All(), 
      $this->DefaultEvents($attributeType)->All(), $row
    );
  }

  private function InsertValues(
    DataList $data
  ): bool {
    $headers = array_keys(
      $data->Copy()->First()
    );

    $data
      ->Chunk(500)
      ->Mapper(
          fn(DataList $chunkRow) => $chunkRow->Mapper(
            fn(array $row) => Util::JoinWithComma($row, "(%s)")
          )
        )
      ->Mapper(
        fn(DataList $chunkRow) => sprintf(
          "Insert into {$this->structureTable->table} %s values %s", ...[
            Util::JoinWithComma($headers, "(%s)"), $chunkRow->JoinWithComma()
          ]
        )
      )
      ->ForEach(
        fn(string $script) => (
          $this->Connect()->Exec($script)
        )
      );

    return true;
  }

  public function Insert(
    array|callable $data = []
  ): bool {
    if(is_callable($data) === true){
      $data = (
        DataByFn::Create(
          $data
        )->getData()
      );
    }

    [ $dataList, $columns ] = [
      DataList::Create($data), $this->Columns()
    ];

    $this->InsertValues(
      $dataList->Mapper(
        fn(array $data) => (
          $this->ParseEncode(
            $this->ParseDefaults(
              $data, AttributeType::Insert
            ), $columns
          )
        )
      )
    );
    
    return true;
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
    return (
      $this->Connect()
        ->Query($sql)
        ->Mapper(fn(object $row) => (
          $this->ParseDecode(
            DataList::Create([$row]), DataList::Create($this->Columns())
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