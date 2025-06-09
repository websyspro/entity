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

  public function SetInicial(
  ): void {
    $this->updateScripts = DataList::Create();
  }

  public function SetAdd(
  ): void {
    if($this->persistedGenerationsList->List()->Exist() === false){
      if($this->structureTable->Generations()->List()->Exist() === true){
        $this->structureTable->Generations()->List()->Where(
          fn(IProperties $property) => (
            $this->updateScripts->Add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Modify Column {$property->name} {$this->structureTable->Columns()->Type($property->name)} {$this->structureTable->Requireds()->Sql($property->name)} Auto_Increment",
                "Column {$property->name} added AutoIncrement with successfully to {$this->structureTable->table}", ScriptType::NotDependence
              )
            )
          )
        );
      }
    }
  }

  public function SetModify(
  ): void {
    if($this->persistedGenerationsList->ListNames()->Exist() === true){
      if($this->structureTable->Generations()->ListNames()->Exist() === true){
        $generationsIsEquals = Util::ArrayEquais(
          $this->persistedGenerationsList->ListNames()->All(),
          $this->structureTable->Generations()->ListNames()->All()
        );

        if($generationsIsEquals === false){
          $this->persistedGenerationsList->List()->Mapper(
            fn(IPersistedGeneration $pg) => (
              $this->updateScripts->Add(
                new IUpdateScript(
                  "Alter Table {$this->structureTable->table} Modify Column {$pg->name} {$this->structureTable->Columns()->Type($pg->name)} {$this->structureTable->Requireds()->Sql($pg->name)}",
                  "Column {$pg->name} modify with successfully to {$this->structureTable->table}", ScriptType::NotDependence
                )
              )
            )
          );

          $this->structureTable->Generations()->List()->Mapper(
            fn(IProperties $property) => (
              $this->updateScripts->Add(
                new IUpdateScript(
                  "Alter Table {$this->structureTable->table} Modify Column {$property->name} {$this->structureTable->Columns()->Type($property->name)} {$this->structureTable->Requireds()->Sql($property->name)} Auto_Increment",
                  "Column {$property->name} added AutoIncrement with successfully to {$this->structureTable->table}", ScriptType::NotDependence
                )
              )              
            )
          );
        }
      }
    }
  }

  public function SetDrops(
  ): void {
    if($this->persistedGenerationsList->ListNames()->Exist() === true){
      if($this->structureTable->Generations()->ListNames()->Exist() === false){
        $this->persistedGenerationsList->List()->Mapper(
          fn(IPersistedGeneration $pg) => (
            $this->updateScripts->Add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Modify Column {$pg->name} {$this->structureTable->Columns()->Type($pg->name)} {$this->structureTable->Requireds()->Sql($pg->name)}",
                "Column {$pg->name} modify with successfully to {$this->structureTable->table}", ScriptType::NotDependence
              )
            )            
          )
        );
      }
    } 
  }

  public function StartUpdates(
  ): MySqlUpdateGenerations {
    $this->SetInicial();
    $this->SetAdd();
    $this->SetModify();
    $this->SetDrops();  
    return $this;
  }

  public function UpdateScripts(
  ): DataList {
    return $this->updateScripts;
  }  
}