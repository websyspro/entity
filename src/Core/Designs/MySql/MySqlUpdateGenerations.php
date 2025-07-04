<?php

namespace Websyspro\Entity\Core\Designs\MySql;

use Websyspro\Commons\DataList;
use Websyspro\Commons\Util;
use Websyspro\Entity\Core\Persisteds\PersistedGenerationsList;
use Websyspro\Entity\Core\StructureTable;
use Websyspro\Entity\Enums\ScriptType;
use Websyspro\Entity\Interfaces\IPersistedGeneration;
use Websyspro\Entity\Interfaces\IProperties;
use Websyspro\Entity\Interfaces\IUpdateScript;

class MySqlUpdateGenerations
{
  public DataList $updateScripts;

  public function __construct(
    public PersistedGenerationsList $persistedGenerationsList,
    public StructureTable $structureTable
  ){}

  public function setInicial(
  ): void {
    $this->updateScripts = DataList::create();
  }

  public function setAdd(
  ): void {
    if($this->persistedGenerationsList->list()->exist() === false){
      if($this->structureTable->generations()->list()->exist() === true){
        $this->structureTable->generations()->list()->where(
          fn(IProperties $property) => (
            $this->updateScripts->add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Modify Column {$property->name} {$this->structureTable->columns()->type($property->name)} {$this->structureTable->requireds()->sql($property->name)} Auto_Increment",
                "Column {$property->name} added AutoIncrement with successfully to {$this->structureTable->table}", ScriptType::notDependence
              )
            )
          )
        );
      }
    }
  }

  public function setModify(
  ): void {
    if($this->persistedGenerationsList->listNames()->exist() === true){
      if($this->structureTable->generations()->listNames()->exist() === true){
        $generationsIsEquals = Util::arrayEquais(
          $this->persistedGenerationsList->listNames()->all(),
          $this->structureTable->generations()->listNames()->all()
        );

        if($generationsIsEquals === false){
          $this->persistedGenerationsList->list()->mapper(
            fn(IPersistedGeneration $pg) => (
              $this->updateScripts->add(
                new IUpdateScript(
                  "Alter Table {$this->structureTable->table} Modify Column {$pg->name} {$this->structureTable->columns()->type($pg->name)} {$this->structureTable->requireds()->sql($pg->name)}",
                  "Column {$pg->name} modify with successfully to {$this->structureTable->table}", ScriptType::notDependence
                )
              )
            )
          );

          $this->structureTable->generations()->list()->mapper(
            fn(IProperties $property) => (
              $this->updateScripts->add(
                new IUpdateScript(
                  "Alter Table {$this->structureTable->table} Modify Column {$property->name} {$this->structureTable->columns()->type($property->name)} {$this->structureTable->requireds()->sql($property->name)} Auto_Increment",
                  "Column {$property->name} added AutoIncrement with successfully to {$this->structureTable->table}", ScriptType::notDependence
                )
              )              
            )
          );
        }
      }
    }
  }

  public function setDrops(
  ): void {
    if($this->persistedGenerationsList->listNames()->exist() === true){
      if($this->structureTable->generations()->listNames()->exist() === false){
        $this->persistedGenerationsList->list()->mapper(
          fn(IPersistedGeneration $pg) => (
            $this->updateScripts->add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Modify Column {$pg->name} {$this->structureTable->columns()->type($pg->name)} {$this->structureTable->requireds()->sql($pg->name)}",
                "Column {$pg->name} modify with successfully to {$this->structureTable->table}", ScriptType::notDependence
              )
            )            
          )
        );
      }
    } 
  }

  public function startUpdates(
  ): MySqlUpdateGenerations {
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