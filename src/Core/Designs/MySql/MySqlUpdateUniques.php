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

  public function SetInicial(
  ): void {
    $this->updateScripts = DataList::Create();
  }

  public function SetAdd(
  ): void {
    if($this->persistedUniquesList->ListNames()->Exist() === false){
      if($this->structureTable->Uniques()->ListNames()->Exist() === true){
        $this->structureTable->Uniques()->ListNames()->Mapper(
          fn(IUniqueNameItems $uniqueNameItems) => (
            $this->updateScripts->Add(
              new IUpdateScript(
                "Alter table {$this->structureTable->table} add constraint {$uniqueNameItems->name} unique ({$uniqueNameItems->columns})",
                "Constraint unique {$uniqueNameItems->name} added with successfully to {$this->structureTable->table}", ScriptType::NotDependence
              )
            )
          )
        );
      }
    }
  }

  public function SetModify(
  ): void {
    if($this->persistedUniquesList->ListNames()->Exist() === true){
      if($this->structureTable->Uniques()->ListNames()->Exist() === true){
        $this->structureTable->Uniques()->ListNames()
          ->Where(fn(IUniqueNameItems $uniqueNameItems) => (
            $this->persistedUniquesList->IsUnique(
              $uniqueNameItems->name
            ) === false
          ))
          ->Mapper(fn(IUniqueNameItems $uniqueNameItems) => (
            $this->updateScripts->Add(
              new IUpdateScript(
                "Alter table {$this->structureTable->table} add constraint {$uniqueNameItems->name} unique ({$uniqueNameItems->columns})",
                "Constraint unique {$uniqueNameItems->name} added with successfully to {$this->structureTable->table}", ScriptType::NotDependence
              )
            )            
          ));
      }
    }    
  }

  public function SetDrops(
  ): void {
    if($this->persistedUniquesList->ListNames()->Exist() === true){
      $this->persistedUniquesList->ListNames()
        ->Where(
          fn(string $uniqueName) => (
            $this->structureTable->Uniques()->IsUnique($uniqueName) === false
          )
        )
        ->Mapper(
          fn(string $uniqueName) => (
            $this->updateScripts->Add(
              new IUpdateScript(
                "alter table {$this->structureTable->table} drop constraint {$uniqueName}",
                "Constraint unique {$uniqueName} drop with successfully to {$this->structureTable->table}", ScriptType::NotDependence
              )
            )
          )
        );
    }
  }

  public function StartUpdates(
  ): MySqlUpdateUniques {
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