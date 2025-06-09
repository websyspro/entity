<?php

namespace Websyspro\Entity\Core\Designs\MySql;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\Persisteds\PersistedForeignKeysList;
use Websyspro\Entity\Core\Shareds\ForeignKeyItem;
use Websyspro\Entity\Core\StructureTable;
use Websyspro\Entity\Enums\ScriptType;
use Websyspro\Entity\Interfaces\IPersistedForeignKeys;
use Websyspro\Entity\Interfaces\IUpdateScript;

class MySqlUpdateForeignKeys
{
  public DataList $updateScripts;

  public function __construct(
    public PersistedForeignKeysList $persistedForeignKeysList,
    public StructureTable $structureTable
  ){}

  private function SetInicial(
  ): void {
    $this->updateScripts = DataList::Create();
  }

  private function SetAdd(
  ): void {
    if($this->persistedForeignKeysList->ListNames()->Exist() === false){
      if($this->structureTable->ForeignKeys()->ListNames($this->structureTable->table)->Exist() === true){
        $this->structureTable->ForeignKeys()->ListNames($this->structureTable->table)
          ->Mapper(fn(ForeignKeyItem $foreignKeyItem) => (
            $this->updateScripts->Add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Add Constraint {$foreignKeyItem->name} Foreign Key ({$foreignKeyItem->key}) References {$foreignKeyItem->foreignKeyReferenceItem->table}({$foreignKeyItem->foreignKeyReferenceItem->key})",
                "Foreign key constraint {$foreignKeyItem->name} added with successfully to {$this->structureTable->table}", ScriptType::Dependence
              )
            )
          ));
      }
    }
  }  

  private function SetModify(
  ): void {
    if($this->persistedForeignKeysList->ListNames()->Exist() === true){
      if($this->structureTable->ForeignKeys()->ListNames($this->structureTable->table)->Exist() === true){
        $this->structureTable->ForeignKeys()->ListNames($this->structureTable->table)
          ->Where(fn(ForeignKeyItem $foreignKeyItem) => (
            $this->persistedForeignKeysList->IsForeignKey(
              $foreignKeyItem->name
            ) === false
          ))
          ->Mapper(fn(ForeignKeyItem $foreignKeyItem) => (
            $this->updateScripts->Add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Add Constraint {$foreignKeyItem->name} Foreign Key ({$foreignKeyItem->key}) References {$foreignKeyItem->foreignKeyReferenceItem->table}({$foreignKeyItem->foreignKeyReferenceItem->key})",
                "Foreign key constraint {$foreignKeyItem->name} added with successfully to {$this->structureTable->table}", ScriptType::Dependence
              )
            )            
          ));
      }
    }
  }

  private function SetDrops(
  ): void {
    if($this->persistedForeignKeysList->ListNames()->Exist() === true){
      $this->persistedForeignKeysList->ListNames()
        ->Where(fn(IPersistedForeignKeys $persistedForeignKeys) => (
          $this->structureTable->ForeignKeys()
            ->IsForeignKey($this->structureTable->table, $persistedForeignKeys->name) === false
        ))
        ->Mapper(
          function(IPersistedForeignKeys $persistedForeignKeys){
            $this->updateScripts->Add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Drop Foreign Key {$persistedForeignKeys->name}",
                "Foreign key constraint {$persistedForeignKeys->name} drop with successfully to {$this->structureTable->table}", ScriptType::Dependence
              )
            );
          }
        );
    }
  }

  public function StartUpdates(
  ): MySqlUpdateForeignKeys {
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