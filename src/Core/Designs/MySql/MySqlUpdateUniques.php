<?php

namespace Websyspro\Entity\Core\Designs\MySql;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\Persisteds\PersistedUniquesList;
use Websyspro\Entity\Core\StructureTable;
use Websyspro\Entity\Enums\ScriptType;
use Websyspro\Entity\Interfaces\IUniqueNameItems;
use Websyspro\Entity\Interfaces\IUpdateScript;

class MySqlUpdateUniques
{
  public DataList $updateScripts;

  public function __construct(
    public PersistedUniquesList $persistedUniquesList,
    public StructureTable $structureTable
  ){}

  public function setInicial(
  ): void {
    $this->updateScripts = DataList::create();
  }

  public function setAdd(
  ): void {
    if($this->persistedUniquesList->listNames()->exist() === false){
      if($this->structureTable->uniques()->listNames()->exist() === true){
        $this->structureTable->uniques()->listNames()->mapper(
          fn(IUniqueNameItems $uniqueNameItems) => (
            $this->updateScripts->add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Add Constraint {$uniqueNameItems->name} Unique ({$uniqueNameItems->columns})",
                "Constraint unique {$uniqueNameItems->name} added with successfully to {$this->structureTable->table}", ScriptType::notDependence
              )
            )
          )
        );
      }
    }
  }

  public function setModify(
  ): void {
    if($this->persistedUniquesList->listNames()->exist() === true){
      if($this->structureTable->uniques()->listNames()->exist() === true){
        $this->structureTable->uniques()->listNames()
          ->where(fn(IUniqueNameItems $uniqueNameItems) => (
            $this->persistedUniquesList->isUnique(
              $uniqueNameItems->name
            ) === false
          ))
          ->mapper(fn(IUniqueNameItems $uniqueNameItems) => (
            $this->updateScripts->add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Add Constraint {$uniqueNameItems->name} Unique ({$uniqueNameItems->columns})",
                "Constraint unique {$uniqueNameItems->name} added with successfully to {$this->structureTable->table}", ScriptType::notDependence
              )
            )            
          ));
      }
    }    
  }

  public function setDrops(
  ): void {
    if($this->persistedUniquesList->listNames()->exist() === true){
      $this->persistedUniquesList->listNames()
        ->where(
          fn(string $uniqueName) => (
            $this->structureTable->uniques()->isUnique($uniqueName) === false
          )
        )
        ->mapper(
          fn(string $uniqueName) => (
            $this->updateScripts->add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Drop Constraint {$uniqueName}",
                "Constraint unique {$uniqueName} drop with successfully to {$this->structureTable->table}", ScriptType::notDependence
              )
            )
          )
        );
    }
  }

  public function startUpdates(
  ): MySqlUpdateUniques {
    $this->setInicial();
    $this->setAdd();
    $this->setModify();
    $this->setDrops();  
    return $this;
  }

  public function updateScripts(
  ): DataList {
    return $this->updateScripts;
  }  
}