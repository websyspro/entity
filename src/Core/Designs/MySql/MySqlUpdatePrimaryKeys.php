<?php

namespace Websyspro\Entity\Core\Designs\MySql;

use Websyspro\Commons\DataList;
use Websyspro\Commons\Util;
use Websyspro\Entity\Core\Persisteds\PersistedPrimaryKeysList;
use Websyspro\Entity\Core\StructureTable;
use Websyspro\Entity\Enums\ScriptType;
use Websyspro\Entity\Interfaces\IUpdateScript;

class MySqlUpdatePrimaryKeys
{
  public DataList $updateScripts;

  public function __construct(
    public PersistedPrimaryKeysList $persistedPrimaryKeysList,
    public StructureTable $structureTable
  ){}

  public function SetStarteds(
  ): void {
    $this->updateScripts = (
      DataList::Create()
    );
  }

  public function SetModify(
  ): void {
    if($this->structureTable->PrimaryKeys()->List()->Exist() === true){
      $primaryKeysIsEquals = Util::ArrayEquais(
        $this->structureTable->PrimaryKeys()->List()->All(),
        $this->persistedPrimaryKeysList->List()->All()
      );

      if($primaryKeysIsEquals === false){
        if($this->persistedPrimaryKeysList->List()->Exist()){
          $this->updateScripts->Add(
            new IUpdateScript(
              "Alter table {$this->structureTable->table} drop primary key",
              "Primary key ({$this->persistedPrimaryKeysList->List()->JoinWithComma()}) create for {$this->structureTable->table} table successfully", ScriptType::NotDependence
            )
          );          
        }

        $this->updateScripts->Add(
          new IUpdateScript(
            "Alter table {$this->structureTable->table} add primary key ({$this->structureTable->PrimaryKeys()->List()->JoinWithComma()})",
            "Primary key ({$this->structureTable->PrimaryKeys()->List()->JoinWithComma()}) create for {$this->structureTable->table} table successfully", ScriptType::NotDependence
          )
        );         
      }
    }
  }

  public function SetDrops(
  ): void {
    if($this->persistedPrimaryKeysList->List()->Exist() === true){
      if($this->structureTable->PrimaryKeys()->List()->Exist() === false){
        $this->updateScripts->Add(
          new IUpdateScript(
            "Alter table {$this->structureTable->table} drop primary key",
            "Primary key ({$this->persistedPrimaryKeysList->List()->JoinWithComma()}) create for {$this->structureTable->table} table successfully", ScriptType::NotDependence
          )
        );        
      }
    }
  }  

  public function StartUpdates(
  ): MySqlUpdatePrimaryKeys {
    $this->SetStarteds();
    $this->SetModify();
    $this->SetDrops(); 
    return $this;
  }

  public function UpdateScripts(
  ): DataList {
    return $this->updateScripts;
  }  
}