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

  private function setInicial(
  ): void {
    $this->updateScripts = DataList::Create();
  }

  private function setAdd(
  ): void {
    if($this->persistedForeignKeysList->listNames()->exist() === false){
      if($this->structureTable->foreignKeys()->listNames($this->structureTable->table)->exist() === true){
        $this->structureTable->foreignKeys()->listNames($this->structureTable->table)
          ->mapper(fn(ForeignKeyItem $foreignKeyItem) => (
            $this->updateScripts->add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Add Constraint {$foreignKeyItem->name} Foreign Key ({$foreignKeyItem->key}) References {$foreignKeyItem->foreignKeyReferenceItem->table}({$foreignKeyItem->foreignKeyReferenceItem->key})",
                "Foreign key constraint {$foreignKeyItem->name} added with successfully to {$this->structureTable->table}", ScriptType::dependence
              )
            )
          ));
      }
    }
  }  

  private function setModify(
  ): void {
    if($this->persistedForeignKeysList->listNames()->exist() === true){
      if($this->structureTable->foreignKeys()->listNames($this->structureTable->table)->exist() === true){
        $this->structureTable->foreignKeys()->listNames($this->structureTable->table)
          ->where(fn(ForeignKeyItem $foreignKeyItem) => (
            $this->persistedForeignKeysList->isForeignKey(
              $foreignKeyItem->name
            ) === false
          ))
          ->mapper(fn(ForeignKeyItem $foreignKeyItem) => (
            $this->updateScripts->add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Add Constraint {$foreignKeyItem->name} Foreign Key ({$foreignKeyItem->key}) References {$foreignKeyItem->foreignKeyReferenceItem->table}({$foreignKeyItem->foreignKeyReferenceItem->key})",
                "Foreign key constraint {$foreignKeyItem->name} added with successfully to {$this->structureTable->table}", ScriptType::dependence
              )
            )            
          ));
      }
    }
  }

  private function setDrops(
  ): void {
    if($this->persistedForeignKeysList->listNames()->exist() === true){
      $this->persistedForeignKeysList->listNames()
        ->where(fn(IPersistedForeignKeys $persistedForeignKeys) => (
          $this->structureTable->foreignKeys()
            ->isForeignKey($this->structureTable->table, $persistedForeignKeys->name) === false
        ))
        ->mapper(
          function(IPersistedForeignKeys $persistedForeignKeys){
            $this->updateScripts->add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Drop Foreign Key {$persistedForeignKeys->name}",
                "Foreign key constraint {$persistedForeignKeys->name} drop with successfully to {$this->structureTable->table}", ScriptType::dependence
              )
            );
          }
        );
    }
  }

  public function startUpdates(
  ): MySqlUpdateForeignKeys {
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