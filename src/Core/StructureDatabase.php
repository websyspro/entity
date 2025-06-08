<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Commons\Reflect;
use Websyspro\Commons\Util;
use Websyspro\Database\Connect;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdateColumns;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdateForeignKeys;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdateGenerations;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdatePrimaryKeys;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdateStatistics;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdateUniques;
use Websyspro\Entity\Core\Persisteds\MySqlScript;
use Websyspro\Entity\Core\Persisteds\PersistedColumnsList;
use Websyspro\Entity\Core\Persisteds\PersistedPrimaryKeysList;
use Websyspro\Entity\Core\Persisteds\PersistedRequiredsList;
use Websyspro\Entity\Enums\ScriptType;
use Websyspro\Entity\Interfaces\IPersistedColumn;
use Websyspro\Entity\Interfaces\IPersistedForeignKeys;
use Websyspro\Entity\Interfaces\IPersistedGeneration;
use Websyspro\Entity\Interfaces\IPersistedPrimaryKey;
use Websyspro\Entity\Interfaces\IPersistedRequireds;
use Websyspro\Entity\Interfaces\IPersistedStatistics;
use Websyspro\Entity\Interfaces\IPersistedUnique;
use Websyspro\Entity\Interfaces\IUpdateScript;
use Websyspro\Logger\Enums\LogType;
use Websyspro\Logger\Message;

class StructureDatabase
{
  public Connect $connect;
  public DataList $updateScripts;
  public DataList $structureTable;
  public DataList $persistedColumn;
  public DataList $persistedPrimaryKeys;
  public DataList $persistedGenerations;
  public DataList $persistedRequireds;
  public DataList $persistedUniques;
  public DataList $persistedStatistics;
  public DataList $persistedForeignKeys;

  public function __construct(
    public string $class
  ){}

  public function GetDatabase(
  ): void {
    $this->connect = (
      Connect::Set(
        strtolower(
          Util::ClassName(
            $this->class
          )
        )
      )
    );
  }

  private function GetDecorationEntitys(
  ): void {
    $reflectClass = (
      Reflect::Class(
        $this->class
      )
    );

    [$attributeEntites] = $reflectClass->getAttributes();
    if($attributeEntites !== null){
      $this->structureTable = new DataList(
        $attributeEntites->newInstance()->entitys
      );

      $this->structureTable->Mapper(
        fn(string $class) => new StructureTable($class)
      );
    }
  }

  private function Get(
    string $query
  ): DataList {
    return $this->connect->Query($query);
  }

  private function SetPersistedsColumns(
  ): DataList {
    return $this->Get(
      MySqlScript::Columns(
        $this->connect->Database()
      )
    )->Mapper(fn(object $obj) => (
      new IPersistedColumn(
        ...(array)$obj
      )
    ));
  }

  private function SetPersistedsRequireds(
  ): DataList {
    return $this->Get(
      MySqlScript::Requireds(
        $this->connect->Database()
      )
    )->Mapper(fn(object $obj) => (
      new IPersistedRequireds(
        ...(array)$obj
      )
    ));
  }  

  private function SetPersistedsPrimaryKeys(
  ): DataList {
    return $this->Get(
      MySqlScript::PrimaryKeys(
        $this->connect->Database()
      )
    )->Mapper(fn(object $obj) => (
      new IPersistedPrimaryKey(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsGenerations(
  ): DataList {
    return $this->Get(
      MySqlScript::Generations(
        $this->connect->Database()
      )
    )->Mapper(fn(object $obj) => (
      new IPersistedGeneration(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsUniques(
  ): DataList {
    return $this->Get(
      MySqlScript::Uniques(
        $this->connect->Database()
      )
    )->Mapper(fn(object $obj) => (
      new IPersistedUnique(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsStatistics(
  ): DataList {
    return $this->Get(
      MySqlScript::Statistics(
        $this->connect->Database()
      )
    )->Mapper(fn(object $obj) => (
      new IPersistedStatistics(
        ...(array)$obj
      )
    ));
  }

  private function SetPersistedsForeignKeys(
  ): DataList {
    return $this->Get(
      MySqlScript::ForeignKeys(
        $this->connect->Database()
      )
    )->Mapper(fn(object $obj) => (
      new IPersistedForeignKeys(
        ...(array)$obj
      )
    ));
  }  

  private function GetPersistedsEntitys(
  ): void {
    if(isset($this->structureTable)){
      $this->persistedColumn = $this->SetPersistedsColumns();
      $this->persistedRequireds = $this->SetPersistedsRequireds();
      $this->persistedForeignKeys = $this->SetPersistedsPrimaryKeys();
      $this->persistedGenerations = $this->SetPersistedsGenerations();
      $this->persistedUniques = $this->SetPersistedsUniques();
      $this->persistedStatistics = $this->SetPersistedsStatistics();
      $this->persistedForeignKeys = $this->SetPersistedsForeignKeys();
    }
  }

  private function GetPersistedColumns(
    StructureTable $structureTable
  ): PersistedColumnsList {
    if(isset($this->persistedColumn) === false){
      return new PersistedColumnsList(
        DataList::Create()
      );
    }

    return new PersistedColumnsList(
      $this->persistedColumn->Copy()->Where(
        fn(IPersistedColumn $persistedColumn) => (
          $persistedColumn->table === $structureTable->table
        )
      )
    );
  }

  private function GetPersistedRequireds(
    StructureTable $structureTable
  ): PersistedRequiredsList {
    return new PersistedRequiredsList( 
      $this->persistedRequireds->Copy()->Where(
        fn(IPersistedRequireds $persistedRequireds) => (
          $persistedRequireds->table === $structureTable->table
        )
      )
    );
  }
  
  private function GetPersistedPrimaryKeys(
    StructureTable $structureTable
  ): PersistedPrimaryKeysList {
    if(isset($this->persistedPrimaryKeys) === false){
      return new PersistedPrimaryKeysList(
        DataList::Create()
      );
    }

    return new PersistedPrimaryKeysList(
      $this->persistedPrimaryKeys->Copy()->Where(
        fn(IPersistedPrimaryKey $persistedPrimaryKey) => (
          $persistedPrimaryKey->table === $structureTable->table
        )
      )
    );
  }

  private function GetPersistedGenerations(
    StructureTable $structureTable
  ): DataList {
    if(isset($this->persistedGenerations) === false){
      return DataList::Create();
    }

    return $this->persistedGenerations->Copy()->Where(
      fn(IPersistedGeneration $persistedGeneration) => (
        $persistedGeneration->table === $structureTable->table
      )
    );
  }

  private function GetPersistedUniques(
    StructureTable $structureTable
  ): DataList {
    if(isset($this->persistedUniques) === false){
      return DataList::Create();
    }

    return $this->persistedUniques->Copy()->Where(
      fn(IPersistedUnique $persistedUnique) => (
        $persistedUnique->table === $structureTable->table
      )
    );
  }

  private function GetPersistedStatistics(
    StructureTable $structureTable
  ): DataList {
    if(isset($this->persistedStatistics) === false){
      return DataList::Create();
    }

    return $this->persistedStatistics->Copy()->Where(
      fn(IPersistedStatistics $persistedStatistic) => (
        $persistedStatistic->table === $structureTable->table
      )
    );
  }

  private function GetPersistedForeignKeys(
    StructureTable $structureTable
  ): DataList {
    if(isset($this->persistedForeignKeys) === false){
      return DataList::Create();
    }

    return $this->persistedForeignKeys->Copy()->Where(
      fn(IPersistedForeignKeys $persistedForeignKey) => (
        $persistedForeignKey->table === $structureTable->table
      )
    );
  }  

  private function AddUpdateScripts(
    DataList $updateScripts
  ): void {
    if(isset($this->updateScripts) === false){
      $this->updateScripts = (
        DataList::Create()
      );
    }
    
    $updateScripts->ForEach(
      fn(IUpdateScript $updateScripts) => (
        $this->updateScripts->Add(
          $updateScripts
        )
      )
    );
  }

  private function GetUpdateStructureColumns(
    StructureTable $structureTable
  ): void {
    $this->AddUpdateScripts(
      (new MySqlUpdateColumns(
        $this->GetPersistedColumns($structureTable),
        $this->GetPersistedRequireds($structureTable), $structureTable
      ))->StartUpdates()->UpdateScripts()
    );
  }

  private function GetUpdateStructurePrimaryKeys(
    StructureTable $structureTable
  ): void {
    $this->AddUpdateScripts(
      (new MySqlUpdatePrimaryKeys(
        $this->GetPersistedPrimaryKeys($structureTable), $structureTable
      ))->StartUpdates()->UpdateScripts()
    );
  }

  private function GetUpdateStructureGenerations(
    StructureTable $structureTable
  ): void {
    $this->AddUpdateScripts(
      (new MySqlUpdateGenerations(
        $this->GetPersistedGenerations($structureTable), $structureTable
      ))->StartUpdates()->UpdateScripts()
    );
  }

  private function GetUpdateStructureUniques(
    StructureTable $structureTable
  ): void {
    $this->AddUpdateScripts(
      (new MySqlUpdateUniques(
        $this->GetPersistedUniques($structureTable), $structureTable
      ))->StartUpdates()->UpdateScripts()
    );
  }

  private function GetUpdateStructureStatistics(
    StructureTable $structureTable
  ): void {
    $this->AddUpdateScripts(
      (new MySqlUpdateStatistics(
        $this->GetPersistedStatistics($structureTable), $structureTable
      ))->StartUpdates()->UpdateScripts()
    );
  }

  private function GetUpdateStructureForeignKeys(
    StructureTable $structureTable
  ): void {
    $this->AddUpdateScripts(
      (new MySqlUpdateForeignKeys(
        $this->GetPersistedForeignKeys($structureTable), $structureTable
      ))->StartUpdates()->UpdateScripts()
    );
  }  

  private function GetUpdateEntitys(
  ): void {
    $this->structureTable->ForEach(
      function(StructureTable $structureTable){
        $this->GetUpdateStructureColumns($structureTable);
        $this->GetUpdateStructurePrimaryKeys($structureTable);
        $this->GetUpdateStructureGenerations($structureTable);
        $this->GetUpdateStructureUniques($structureTable);
        $this->GetUpdateStructureStatistics($structureTable);
        $this->GetUpdateStructureForeignKeys($structureTable);
      }
    );
  }

  private function ExecuteDatabase(
    IUpdateScript $updateScript
  ): void {
    if($this->connect->Exec($updateScript->sql) === true){
      Message::Infors(LogType::Database, $updateScript->message);
    }
  }

  private function SetUpdateDatabase(
  ): void {
    $updateScriptsNotDependence = $this->updateScripts->Copy()->Where(
      fn(IUpdateScript $updateScript) => (
        $updateScript->scriptType === ScriptType::NotDependence
      )
    );

    $updateScriptsDependence = $this->updateScripts->Copy()->Where(
      fn(IUpdateScript $updateScript) => (
        $updateScript->scriptType === ScriptType::Dependence
      )
    );    

    $updateScriptsNotDependence->ForEach(fn(IUpdateScript $updateScript) => $this->ExecuteDatabase($updateScript));
    $updateScriptsDependence->ForEach(fn(IUpdateScript $updateScript) => $this->ExecuteDatabase($updateScript));
  }
  
  public function Update(
  ): void {
    $this->GetDatabase();
    $this->GetDecorationEntitys();
    $this->GetPersistedsEntitys();
    $this->GetUpdateEntitys();
    $this->SetUpdateDatabase();
  }
}