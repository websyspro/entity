<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\Shareds\ForeignKeyItem;
use Websyspro\Entity\Core\StructureTable;

class IEntityGroup
{
  public DataList $rowList;
  public DataList $primaryKeys;
  public DataList $foreignKeys;
  public DataList $oneToOne;
  public DataList $oneToMany;

  public function __construct(
    public StructureTable $structure,
    public DataList $queryRows,
    public String $alias,
  ){
    $this->defineOneToOne();
    $this->definePrimaryKey();
    $this->defineFilter();
    $this->defineClear();
  }

  private function defineOneToOne(
  ): void {
    $this->oneToOne = (
      $this->structure
        ->foreignKeys()
        ->listNames($this->structure->table)
        ->mapper(
          fn(ForeignKeyItem $fk) => (
            new IOneToOne(
              $fk->key,
              $fk->foreignKeyReferenceItem->table,
              $fk->foreignKeyReferenceItem->key
            )
          )
        )
    );
  }

  public function defineOneToMany(
    DataList $entityGroupList
  ): void {
    $oneToMany = [];

    foreach($entityGroupList->all() as $entityGroup){
      if($entityGroup instanceof IEntityGroup){
        $foreingsKeysList = $entityGroup->structure
          ->foreignKeys()->listNames(
            $entityGroup->structure->table
          );

        foreach($foreingsKeysList->all() as $foreingsKeys){
          if($foreingsKeys instanceof ForeignKeyItem){
            if($foreingsKeys->foreignKeyReferenceItem->table === $this->structure->table){
              $oneToMany[] = new IOneToMany(
                $foreingsKeys->foreignKeyReferenceItem->key,
                $foreingsKeys->table,
                $foreingsKeys->key
              );
            }
          }
        }
      }
    }

    $this->oneToMany = (
      DataList::create(
        $oneToMany
      )
    );
  } 

  private function definePrimaryKey(
  ): void {
    $this->primaryKeys = DataList::create(
      array_flip($this->structure->primaryKeys()->list()->all())
    );
  }

  private function defineFilter(
  ): void {
    if($this->queryRows->exist() === true){
      $this->rowList = DataList::create();

      foreach($this->queryRows->all() as $row){
        $rowNew = [];

        foreach($row as $column => $value){
          if(preg_match("#^{$this->alias}_\.*#", $column) === 1){
            $rowNew[preg_replace("/^{$this->alias}_/", "", $column)] = $value;
          }
        }

        $this->primaryKeys->mapper(
          fn(mixed $val, string $key) => $rowNew[$key] 
        );

        if($this->rowList->count() === 0){
          $this->rowList->add($rowNew);
        } else {
          $hasQueryRowsFilter = (
            $this->rowList->copy()->where(
              fn(array $row) => (
                $this->primaryKeys->copy()->where(
                  fn(mixed $val, string $key) => (
                    isset($row[$key]) === true && $row[$key] === $val
                  )
                )->exist()
              )
            )
          );

          if($hasQueryRowsFilter->exist() === false){
            $this->rowList->add($rowNew);
          }
        }
      }
    }
  }

  public function defineClear(
  ): void {
    unset($this->primaryKeys);
    unset($this->queryRows);
    unset($this->alias);
  }
}