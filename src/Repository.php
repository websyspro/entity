<?php

namespace Websyspro\Entity;

use Websyspro\Commons\DataList;
use Websyspro\Commons\Util;
use Websyspro\Database\Connect;
use Websyspro\DynamicSql\Core\DataByFn;
use Websyspro\DynamicSql\QueryBuild;
use Websyspro\DynamicSql\Shareds\ItemParameter;
use Websyspro\Entity\Core\Shareds\StdClassToEntity;
use Websyspro\Entity\Core\StructureTable;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\RelationshipType;
use Websyspro\Entity\Interfaces\IEntityGroup;
use Websyspro\Entity\Interfaces\IForeignQueryItem;
use Websyspro\Entity\Interfaces\IOneToMany;
use Websyspro\Entity\Interfaces\IOneToOne;
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
  
  public static function entity(
    string $entity
  ): Repository {
    return new static($entity);
  }

  public function connect(
  ): Connect {
    // $module = (
    //   strtolower(
    //     Util::className(
    //       $this->structureTable->module
    //     )
    //   )
    // );

    return Connect::set("shops");
    //return Connect::set($module);
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
          fn(ItemParameter $i) => new IEntityGroup(
            $i->structureTable, $queryRows, $i->name
          ) 
        );
      }
    } else
    if($queryBuild->hasWhere()){
      if($queryBuild->where->getParameters()->exist() === true){
        $groupRows = $queryBuild->where->getParameters()->copy()->mapper(
          fn(ItemParameter $i) => new IEntityGroup(
            $i->structureTable, $queryRows, $i->name
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

  public function toEntityTree(
    DataList $entityGroupRows
  ): mixed {
        // Mapeia todas as entidades por tabela e por ID
    $entityMap = [];
    $childrenMap = [];

    foreach ($entityGroupRows->all() as $group) {
        $table = $group->structure->table;
        $entityClass = $group->structure->entity;

        foreach ($group->getQueryRowsFilters()->all() as $row) {
            $id = $row['Id'] ?? null;
            if ($id === null) continue;

            if (!isset($entityMap[$table][$id])) {
                $entity = new $entityClass();
                foreach ($row as $prop => $val) {
                    $entity->$prop = $val;
                }
                $entityMap[$table][$id] = $entity;
            }
        }

        // Constrói o mapeamento de filhos para cada relação (join)
        foreach ($group->getForeignKeys()->all() as $fk) {
            $fromTable = $fk->table;
            $fromKey = $fk->tableKey;
            $toTable = $fk->reference;
            $toKey = $fk->referenceKey;

            foreach ($group->getQueryRowsFilters()->all() as $row) {
                $fromId = $row['Id'];
                $refId = $row[$fromKey];

                if (!isset($entityMap[$fromTable][$fromId])) continue;
                if (!isset($entityMap[$toTable][$refId])) continue;

                $fromEntity = $entityMap[$fromTable][$fromId];
                $toEntity = $entityMap[$toTable][$refId];

                $propName = $toTable; // Ex: $document->Customer = ...

                if (!property_exists($fromEntity, $propName)) {
                    continue;
                }

                $fromEntity->$propName = $toEntity;

                // Mapeia inversamente para estruturas de 1:N
                $childrenMap[$toTable][$refId][$fromTable][] = $fromEntity;
            }
        }
    }

    // Verifica se há relações 1:N como Document->Items
    foreach ($entityMap as $table => $entities) {
        foreach ($entities as $id => $entity) {
            if (isset($childrenMap[$table][$id])) {
                foreach ($childrenMap[$table][$id] as $childTable => $list) {
                    $pluralProp = $childTable . 's'; // Ex: Items
                    if (!property_exists($entity, $pluralProp)) {
                        continue;
                    }
                    $entity->$pluralProp = $list;
                }
            }
        }
    }

    // Retorna o primeiro Document (entidade principal)
    return $entityMap['Document'][array_key_first($entityMap['Document'])] ?? null;
  }

  public function entityByTreeOneToOne(
    array $row,
    DataList $foreignKeys,
    DataList $entityGroupOuters,
    DataList $entityGroupList
  ): array {
    $rowList = [];

    $hasOneEntityGroupOuters = $entityGroupOuters->copy()->where(
      fn(IEntityGroup $entityGroup) => $foreignKeys->copy()->where(
        fn(IForeignQueryItem $foreignQueryItem) => $foreignQueryItem->reference === $entityGroup->structure->table
      )->exist()
    );

    if($hasOneEntityGroupOuters->exist() === false){
      return $row;
    }

    foreach($foreignKeys->all() as $foreingKey){
      foreach($hasOneEntityGroupOuters->all() as $entityGroup){
        $oneRow = null;

        if($entityGroup->structure->table === $foreingKey->reference){
          foreach($entityGroup->rowList->all() as $rowOne){
            if($rowOne[$foreingKey->referenceKey] === $row[$foreingKey->tableKey]){
              $entityGroupOuters = $entityGroupList->copy()->where(
                fn(IEntityGroup $entityGroup) => $entityGroup->structure->table !== $foreingKey->table
              );

            
              $oneRow = array_merge($rowOne, $this->entityByTreeOneToOne(
                $rowOne, $entityGroup->foreignKeys, $entityGroupOuters, $entityGroupList
              ));
            }
          }

          if($oneRow !== null){
            $rowList = array_merge($rowList, [$entityGroup->structure->table => $oneRow]);
          } else $rowList = array_merge($rowList, [$entityGroup->structure->table => []]); 
        }
      }
    }

    return $rowList;
  }

  public function entityByTreeOneToMany(
    array $row,
    DataList $foreignKeys,
    DataList $entityGroupOuters,
    DataList $entityGroupList
  ): array {
    $rowList = [];

    $hasOneEntityGroupOuters = $entityGroupOuters->copy()->where(
      fn(IEntityGroup $entityGroup) => $entityGroup->foreignKeys->copy()->where(
        fn(IForeignQueryItem $foreignQueryItem) => $foreignQueryItem->reference === $foreignKeys->first()->table
      )->exist()
    );

    if($hasOneEntityGroupOuters->exist() === false){
      return $row;
    }

    foreach($hasOneEntityGroupOuters->all() as $entityGroup){
      $entityGroupForeignKeys = $entityGroup->foreignKeys->copy()->where(
        fn(IForeignQueryItem $foreignQueryItem) => $foreignQueryItem->reference === $foreignKeys->first()->table
      );

      foreach($entityGroupForeignKeys->all() as $entityGroupForeignKey){
        $manyRow = [];

        foreach($entityGroup->rowList->all() as $rowMany){
          if($rowMany[$entityGroupForeignKey->tableKey] === $row[$entityGroupForeignKey->referenceKey]){
            // $entityGroupOuters = $entityGroupList->copy()->where(
            //   fn(IEntityGroup $entityGroupInner) => $entityGroupInner->structure->table !== $entityGroupForeignKey->table
            // );
            $entityGroupOuters = $entityGroupList->copy()->where(
              fn(IEntityGroup $entityGroup) => $entityGroup->structure->table !== $entityGroupForeignKey->table
            );

            $test = array_merge($rowMany, $this->entityByTreeOneToOne(
              $rowMany, $entityGroupForeignKeys, $entityGroupOuters, $entityGroupList
            ));

            $manyRow[] = $rowMany;
          }
        }
      }

      if(sizeof($manyRow) !== 0){
        $rowList = array_merge($rowList, [$entityGroup->structure->table . "s" => $manyRow]);
      } else $rowList = array_merge($rowList, [$entityGroup->structure->table => []]); 
    }
    
    return $rowList;
  }

  public function entityByTree_(
    string $table,
    string $entity,
    DataList $entityGroupList
  ): DataList {
    $entityGroupBase = $entityGroupList->copy()->where(
      fn(IEntityGroup $entityGroup) => (
        $entityGroup->structure->table === $table
      )
    );

    $entityGroupOuters = $entityGroupList->copy()->where(
      fn(IEntityGroup $entityGroup) => (
        $entityGroup->structure->table !== $table
      )
    );

    if($entityGroupBase->first() instanceof IEntityGroup){
      $rowList = $entityGroupBase->first()->rowList;
      $foreignKeys = $entityGroupBase->first()->foreignKeys;

      $rowList->mapper(
        fn(array $row) => (
          array_merge(
            $row, 
            $this->entityByTreeOneToOne($row, $foreignKeys, $entityGroupOuters, $entityGroupList),
            $this->entityByTreeOneToMany($row, $foreignKeys, $entityGroupOuters, $entityGroupList)
          )
        )
      );
    }

    print_r($rowList);

    //$newList = $this->entityByTreeOneToOne($table, $entityGroupOuters);

    //print_r($entityGroupBase);

    return DataList::create();
  }

  public function entityGroupRelationship(
    array $row,
    IEntityGroup $entityGroupBase,
    DataList $entityGroupList,
    RelationshipType $relationshipType
  ): array {
    if($relationshipType === RelationshipType::oneToOne){
      $entityGroupRelatonshipList = $entityGroupList->copy()->where(
        fn(IEntityGroup $entityGroup) => in_array(
          $entityGroup->structure->table, $entityGroupBase->oneToOne->copy()->mapper(
            fn(IOneToOne $oneToOne) => $oneToOne->reference
          )->all()
        )
      );
    } else
    if($relationshipType === RelationshipType::oneToMany){
      $entityGroupRelatonshipList = $entityGroupList->copy()->where(
        fn(IEntityGroup $entityGroup) => in_array(
          $entityGroup->structure->table, $entityGroupBase->oneToMany->copy()->mapper(
            fn(IOneToMany $oneToOne) => $oneToOne->reference
          )->all()
        )
      );
    }

    if($entityGroupRelatonshipList->exist() === false){
      return $row;
    }

    if($relationshipType === RelationshipType::oneToOne){
      $rowOneToOneList = [];

      foreach($entityGroupBase->oneToOne->all() as $entityGroupBaseOneToOne){
        $entityGroupRelatonship = $entityGroupRelatonshipList->copy()->where(
          fn(IEntityGroup $entityGroup) => $entityGroup->structure->table === $entityGroupBaseOneToOne->reference
        );
        
        foreach($entityGroupRelatonship->first()->rowList->all() as $rowList){
          if($row[$entityGroupBaseOneToOne->key] === $rowList[$entityGroupBaseOneToOne->referenceKey]){
            $rowList = array_merge( $rowList,
              $this->entityGroupRelationship($rowList, $entityGroupRelatonship->first(), $entityGroupList, RelationshipType::oneToOne),
              $this->entityGroupRelationship($rowList, $entityGroupRelatonship->first(), $entityGroupList, RelationshipType::oneToMany)
            );

            $rowOneToOneList = array_merge(
              $rowOneToOneList, [$entityGroupBaseOneToOne->reference => $rowList]
            );
          }
        }
      }

      return $rowOneToOneList;
    } else
    if($relationshipType === RelationshipType::oneToMany){
      $rowOneToManyList = [];

      foreach($entityGroupBase->oneToMany->all() as $entityGroupBaseOneToMany){
        $rowOneToMany = [];

        $entityGroupRelatonship = $entityGroupRelatonshipList->copy()->where(
          fn(IEntityGroup $entityGroup) => $entityGroup->structure->table === $entityGroupBaseOneToMany->reference
        );

        foreach($entityGroupRelatonship->first()->rowList->all() as $rowList){
          if($row[$entityGroupBaseOneToMany->key] === $rowList[$entityGroupBaseOneToMany->referenceKey]){
            array_merge( $rowList,
              $this->entityGroupRelationship($rowList, $entityGroupRelatonship->first(), $entityGroupList, RelationshipType::oneToMany)
            );

            $rowOneToMany[] = $rowList;
          }
        }

        $rowOneToManyList = array_merge(
          $rowOneToManyList, ["{$entityGroupBaseOneToMany->reference}s" => $rowOneToMany]
        );
      }

      return $rowOneToManyList;
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

    if($entityBase->first() instanceof IEntityGroup){
      $entityBase->first()->rowList->mapper(
        fn(array $row) => array_merge( $row, 
          $this->entityGroupRelationship($row, $entityBase->first(), $entityGroupList, RelationshipType::oneToOne),
          $this->entityGroupRelationship($row, $entityBase->first(), $entityGroupList, RelationshipType::oneToMany)
        )
      );
    }


    return $entityBase->first()->rowList;
  }

  public function queryBuild(
    QueryBuild $queryBuild    
  ): DataList {
    echo $queryBuild->get();
    $queryRows = (
      $this->connect()->query(
        $queryBuild->get()
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

    print_r($entityGroupList);

    //print_r($entityGroupRows);
    // $this->entityByTree(
    //   $this->structureTable->table, 
    //   $this->structureTable->entity, $entityGroupRows
    // );

    
    //print_r();
    return $queryRows;

    // return (
    //   $this->connect()
    //     ->query($queryBuild->get())
    //     ->mapper(fn(object $row) => (
    //       $this->parseDecode(
    //         DataList::create([$row]), DataList::create($this->columns())
    //       )->first()
    //     ))
    // );
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

    $recordFirst = $this->queryBuild(
      $queryBuild
    )->first();

    if($recordFirst === false){
      return new $this->table;
    }

    return $recordFirst;
  }  
}