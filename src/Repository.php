<?php

namespace Websyspro\Entity;

use Websyspro\Commons\DataList;
use Websyspro\Commons\Util;
use Websyspro\Database\Connect;
use Websyspro\DynamicSql\Core\DataByFn;
use Websyspro\DynamicSql\Enums\EDriverType;
use Websyspro\DynamicSql\QueryBuild;
use Websyspro\DynamicSql\Shareds\ItemParameter;
use Websyspro\Entity\Core\Shareds\StdClassToEntity;
use Websyspro\Entity\Core\StructureTable;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\RelationshipType;
use Websyspro\Entity\Interfaces\IEntityGroup;
use Websyspro\Entity\Interfaces\IProperties;

class Repository
{
  public StructureTable $structureTable;

  public mixed $selectFn;
  public mixed $whereFn;
  public mixed $groupByFn;
  public mixed $orderByAscFn;
  public mixed $orderByDescFn;
  public int $limit;
  public int $offSet;

  public function __construct(
    public string $table
  ){
    $this->structureTable = (
      new StructureTable(
        $this->table
      )
    );
  }
  
  public static function entity(
    string $entity
  ): Repository {
    return new static($entity);
  }

  public function connect(
  ): Connect {
    $module = (
      strtolower(
        Util::className(
          $this->structureTable->module
        )
      )
    );

    return Connect::set($module);
  }

  private function columns(
  ): array {
    return (
      $this->structureTable->columns()->list()->reduce(
        [], function(array $curr, IProperties $event){
          $curr[$event->name] = $event->items->first()->columnType;
          return $curr;
        }
      )->all()
    );
  }

  private function defaultEvents(
    AttributeType $attributeType
  ): DataList {
    if(AttributeType::insert === $attributeType){
      $defaultEvents = $this->structureTable->eventInserts()->list();
    } else
    if(AttributeType::update === $attributeType){
      $defaultEvents = $this->structureTable->eventUpdates()->list();
    } else
    if(AttributeType::delete === $attributeType){
      $defaultEvents = $this->structureTable->eventDeletes()->list();
    } 

    if($defaultEvents->exist() === false){
      return DataList::create();
    }

    $defaultEvents->reduce([], function(array $curr, IProperties $event){
      $curr[$event->name] = $event->items->first()->get();
      return $curr;
    });

    return $defaultEvents;
  }

  private function parseEncode(
    array $row,
    array $columns
  ): array {
    return (
      Util::mapper(
        $row, fn(mixed $value, string $key) => (
          $columns[$key]->encode($value)
        )
      )
    );
  }

  private function parseDecode(
    DataList $row,
    DataList $columns
  ): DataList {
    $row->mapper(
      fn(mixed $stdClass) => (
        StdClassToEntity::parse(
          $stdClass, $this->table
        )
      )
    );

    $row->mapper(
      fn(mixed $stdClass) => (
        Util::mapper(
          $stdClass, function(
            mixed $value, 
            string $name
          ) use($columns) {
            return $columns->copy()->whereByKey(
              fn(string $columnName) => $columnName === $name
            )->first()->decode($value);
          }
        )
      )
    );

    return $row;
  }   

  private function parseDefaults(
    array $row,
    AttributeType $attributeType
  ): array {
    return array_merge(
      $this->defaultEvents(
        $attributeType
      )->all(), $row
    );
  }

  private function insertValues(
    DataList $data
  ): DataList {
    $headers = array_keys(
      $data->copy()->first()
    );

    return $data
      ->chunk(500)
      ->mapper(
          fn(DataList $chunkRow) => $chunkRow->mapper(
            fn(array $row) => Util::joinWithComma($row, "(%s)")
          )
        )
      ->mapper(
        fn(DataList $chunkRow) => sprintf(
          "Insert into {$this->structureTable->table} %s values %s", ...[
            Util::joinWithComma($headers, "(%s)"), $chunkRow->joinWithComma()
          ]
        )
      )
      ->mapper(
        fn(string $script) => (
          $this->connect()->exec($script)
        )
      );
  }

  public function insertFromImport(
    array $data = []
  ): bool {
    [ $dataList, $columns ] = [
      DataList::create($data), $this->columns()
    ];

    $this->insertValues(
      $dataList->mapper(
        fn(array $data) => (
          $this->parseEncode(
            $this->parseDefaults(
              $data, AttributeType::insert
            ), $columns
          )
        )
      )
    );

    return true;
  }  

  public function insert(
    array|callable $data = []
  ): object|bool {
    $dataList = DataList::create([
      is_callable($data) === true
        ? DataByFn::create($data)->arrayFromFn()
        : $data
    ]);

    $insertData = (
      $this->insertValues(
        $dataList->mapper(
          fn(array $data) => (
            $this->parseEncode(
              $this->parseDefaults(
                $data, AttributeType::insert
              ), $this->columns()
            )
          )
        )
      )
    );

    if($insertData->count() === 1){
      return $this->getLastId(
        $insertData->first()
      );
    } else return true;
  }

  private function generations(
  ): DataList {
    return (
      $this->structureTable
        ->primaryKeys()
        ->list()
    );
  }

  private function getLastId(
    int $lastId
  ): object {
    [ $GenerationId ] = (
      $this->structureTable
        ->generations()
        ->listNames()
        ->all()
    );

    return (
      $this->connect()->query(
        "Select * 
           From {$this->structureTable->table} 
          Where {$GenerationId}={$lastId}"
      )->mapper(fn(object $object) => (
        StdClassToEntity::parse($object, $this->table)
      ))->first()
    );
  }

  public function updateValues(
    DataList $data
  ): DataList {
    return (
      $data->mapper(
        fn(array $row) => (
          Util::mapper($row, (
            fn(mixed $val, string $key) => "{$key}={$val}"
          ))
        )
      )
      ->mapper(
        fn(array $row) => (
          [ Util::whereByKey($row, fn(string $key) => in_array($key, $this->generations()->all()) === false),
            Util::whereByKey($row, fn(string $key) => in_array($key, $this->generations()->all()) === true) ]
        )
      )
      ->mapper(
        function(array $row){
          [ $updates, $wheres ] = $row;

          return sprintf(
            "Update {$this->structureTable->table} Set %s Where %s", ...[
              Util::join(", ", $updates),
              Util::join(" and ", $wheres),
            ]
          );
        }
      )
      ->mapper(
        fn(string $script) => (
          $this->connect()->exec($script)
        )
      )
    );
  }

  public function update(
    array|callable $data = []
  ): object|bool {
    $dataList = DataList::create([
      is_callable($data) === true
        ? DataByFn::create($data)->arrayFromFn()
        : $data
    ]);

    $updateData = (
      $this->updateValues(
        $dataList->mapper(
          fn(array $data) => (
            $this->parseEncode(
              $this->parseDefaults(
                $data, AttributeType::update
              ), $this->columns()
            )
          )
        )
      )
    );

    if($updateData->count() === 1){
      return true;
    } else return true;
  }

  public function count(
  ): int {
    return $this->connect()->query(
      "Select Count(*) as CountRows 
         From {$this->structureTable->table}"
    )->first()->CountRows;
  }

  public function exists(
  ): bool {
    return $this->connect()->query(
      "Select Count(*) as CountRows From {$this->structureTable->table}"
    )->first()->CountRows !== 0;
  }  

  public function entityGroupList(
    QueryBuild $queryBuild,
    DataList $queryRows
  ): DataList {
  if($queryBuild->hasSelect()){
      if($queryBuild->select->getParameters()->exist() === true){
        $groupRows = $queryBuild->select->getParameters()->copy()->mapper(
          fn(ItemParameter $entityGroup) => new IEntityGroup(
            $entityGroup->structureTable, $queryRows, $entityGroup->name
          ) 
        );
      }
    } else
    if($queryBuild->hasWhere()){
      if($queryBuild->where->getParameters()->exist() === true){
        $groupRows = $queryBuild->where->getParameters()->copy()->mapper(
          fn(ItemParameter $entityGroup) => new IEntityGroup(
            $entityGroup->structureTable, $queryRows, $entityGroup->name
          ) 
        );
      }
    }
    
    return $groupRows;
  }

  public function entityGroupManyList(
    DataList $entityGroupList
  ): DataList {
    foreach($entityGroupList->all() as $entityGroup){
      if($entityGroup instanceof IEntityGroup){
        $entityGroup->defineOneToMany(
          $entityGroupList
        );
      }
    }

    return $entityGroupList;
  }

  private function entityGroupRelationship(
    array $row,
    IEntityGroup $entityGroupBase,
    DataList $entityGroupList,
    RelationshipType $relationshipType
  ): array {
    if($relationshipType === RelationshipType::oneToOne){
      foreach($entityGroupBase->oneToOne->all() as $oneToOne){
        $entityList = $entityGroupList->copy()->where(
          fn(IEntityGroup $entityGroup) => (
            $entityGroup->structure->table === $oneToOne->reference
          )
        );

        if($entityList->exist() === true){
          $oneToOneNames = $entityGroupBase->structure->oneToOnes()->list()->where(
            fn(IProperties $properties) => $entityList->first()->structure->table === (
              new StructureTable($properties->items->first()->referenceClass)
            )->table
          );

          if($entityList->exist() === true && $oneToOneNames->exist() === true){
            foreach($entityList->first()->rowList->all() as $rowList){
              if($row[$oneToOne->key] === $rowList[$oneToOne->referenceKey]){
                $rowList = (
                  StdClassToEntity::parse(
                    array_merge($rowList,
                      $this->entityGroupRelationship(
                        $rowList, $entityList->first(), $entityGroupList, RelationshipType::oneToOne
                      ),
                      $this->entityGroupRelationship(
                        $rowList, $entityList->first(), $entityGroupList, RelationshipType::oneToMany
                      )
                    ), $entityList->first()->structure->entity
                  )
                );

                $row = array_merge($row, [$oneToOneNames->first()->name => $rowList]);
              }
            }
          }
        }
      }

      return $row;
    } else
    if($relationshipType === RelationshipType::oneToMany){
      foreach($entityGroupBase->oneToMany->all() as $oneToMany){
        $entityList = $entityGroupList->copy()->where(
          fn(IEntityGroup $entityGroup) => (
            $entityGroup->structure->table === $oneToMany->reference
          )
        );

        if($entityList->exist() === true){
          $oneToManyNames = $entityGroupBase->structure->oneToManys()->list()->where(
            fn(IProperties $properties) => $entityList->first()->structure->table === (
              new StructureTable($properties->items->first()->referenceClass)
            )->table
          ); 

          if($entityList->exist() === true && $oneToManyNames->exist() === true){
            $rowLists = [];
            foreach($entityList->first()->rowList->all() as $rowList){
              if($row[$oneToMany->key] === $rowList[$oneToMany->referenceKey]){
                $rowList = StdClassToEntity::parse(
                  array_merge( $rowList, 
                    $this->entityGroupRelationship(
                      $rowList, $entityList->first(), $entityGroupList, RelationshipType::oneToOne
                    ),
                    $this->entityGroupRelationship(
                      $rowList, $entityList->first(), $entityGroupList, RelationshipType::oneToMany
                    )
                  ), $entityList->first()->structure->entity
                );

                $rowLists[] = $rowList;
              }
            }

            $row = array_merge($row, [
              $oneToManyNames->first()->name => DataList::create(
                $rowLists
              )
            ]);
          }
        }
      }

      return $row;
    }

    return [];
  }  

  public function entityGroupListToTree(
    DataList $entityGroupList
  ): DataList {
    $entityBase = (
      $entityGroupList
        ->copy()->slice(0, 1)
    );

    if(isset($entityBase->first()->rowList) === false){
      return DataList::create([]);
    }    

    if($entityBase->first() instanceof IEntityGroup){
      $entityBase->first()->rowList->mapper(
        fn(array $row) => (
          StdClassToEntity::parse(
            array_merge($row, 
              $this->entityGroupRelationship($row, $entityBase->first(), $entityGroupList, RelationshipType::oneToOne),
              $this->entityGroupRelationship($row, $entityBase->first(), $entityGroupList, RelationshipType::oneToMany)
            ), $entityBase->first()->structure->entity
          )
        )
      );
    }


    return $entityBase->first()->rowList;
  }

  public function queryBuild(
    QueryBuild $queryBuild    
  ): DataList {
    $queryRows = (
      $this->connect()->query(
        $queryBuild->get(
          $this->connect()->driverType()
        )
      )
    );

    $entityGroupList = (
      $this->entityGroupListToTree(
        $this->entityGroupManyList(
          $this->entityGroupList(
            $queryBuild, $queryRows
          )
        )
      )
    );

    return $entityGroupList;
  }

  public function setProperty(
    string $key,
    mixed $value
  ): Repository {
    $this->{$key} = $value;
    return $this;
  }

  public function select(
    callable $selectFn
  ): Repository {
    return $this->setProperty(
      "selectFn", $selectFn
    );    
  }

  public function where(
    callable $whereFn
  ): Repository {
    return $this->setProperty(
      "whereFn", $whereFn
    );
  }

  public function groupBy(
    callable $groupByFn
  ): Repository {
    return $this->setProperty(
      "groupByFn", $groupByFn
    );
  }

  public function orderByAsc(
    callable $orderByAscFn
  ): Repository {
    return $this->setProperty(
      "orderByAscFn", $orderByAscFn
    );
  }  

  public function orderByDesc(
    callable $orderByDescFn
  ): Repository {
    return $this->setProperty(
      "orderByDescFn", $orderByDescFn
    );
  }

  public function paged(
    int $limit,
    int $offSet
  ): Repository {
    $this->setProperty("limit", $limit);
    $this->setProperty("offSet", $offSet);

    return $this;
  }

  public function all(
  ): DataList {
    $queryBuild = (
      new QueryBuild(
        $this->table
      )
    );

    if(isset($this->selectFn))
      $queryBuild->select($this->selectFn);
    if(isset($this->whereFn))
      $queryBuild->where($this->whereFn);
    if(isset($this->groupByFn))
      $queryBuild->groupBy($this->groupByFn);
    if(isset($this->orderByAscFn))
      $queryBuild->orderByAsc($this->orderByAscFn);
    if(isset($this->orderByDescFn))
      $queryBuild->orderByDesc($this->orderByDescFn);
    if(isset($this->limit) && isset($this->offSet))
      $queryBuild->paged($this->limit, $this->offSet);

    return $this->queryBuild($queryBuild);
  }

  public function one(
  ): object {
    $queryBuild = (
      new QueryBuild(
        $this->table
      )
    );

    if(isset($this->selectFn))
      $queryBuild->select($this->selectFn);
    if(isset($this->whereFn))
      $queryBuild->where($this->whereFn);
    if(isset($this->groupByFn))
      $queryBuild->groupBy($this->groupByFn);
    if(isset($this->orderByAscFn))
      $queryBuild->orderByAsc($this->orderByAscFn);
    if(isset($this->orderByDescFn))
      $queryBuild->orderByDesc($this->orderByDescFn);
    if(isset($this->limit) && isset($this->offSet))
      $queryBuild->paged($this->limit, $this->offSet);    

    $recordFirst = $this->queryBuild(
      $queryBuild
    )->first();

    if($recordFirst === false){
      return new $this->table;
    }

    return $recordFirst;
  }  
}