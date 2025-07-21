<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\Shareds\OneToManyItem;
use Websyspro\Entity\Core\Shareds\OneToOneItem;
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
        ->oneToOnes()
        ->listNames($this->structure->table)
        ->mapper(
          fn(OneToOneItem $fk) => (
            new IOneToOne(
              $fk->key,
              $fk->oneToOneReferenceItem->table,
              $fk->oneToOneReferenceItem->key
            )
          )
        )
    );
  }

  public function defineOneToMany(
  ): void {
    $this->oneToMany = $this->structure
      ->oneToManys()
      ->listNames($this->structure->table)
      ->mapper(fn(OneToManyItem $oneToManyItem) => (
        new IOneToMany(
          $oneToManyItem->name,
          $oneToManyItem->table,
          $oneToManyItem->oneToManyReferenceItem->table,
          $oneToManyItem->oneToManyReferenceItem->key
        )
      ));
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
          if(preg_match("/^{$this->alias}_\.*/", $column) === 1){
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