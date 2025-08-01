<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Commons\Util;
use Websyspro\Database\Connect;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdateColumns;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdateForeignKeys;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdateGenerations;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdateOneToOne;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdatePrimaryKeys;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdateStatistics;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdateUniques;
use Websyspro\Entity\Core\Persisteds\MySqlScript;
use Websyspro\Entity\Core\Persisteds\PersistedColumnsList;
use Websyspro\Entity\Core\Persisteds\PersistedForeignKeysList;
use Websyspro\Entity\Core\Persisteds\PersistedGenerationsList;
use Websyspro\Entity\Core\Persisteds\PersistedOneToOnesList;
use Websyspro\Entity\Core\Persisteds\PersistedPrimaryKeysList;
use Websyspro\Entity\Core\Persisteds\PersistedRequiredsList;
use Websyspro\Entity\Core\Persisteds\PersistedStatisticsList;
use Websyspro\Entity\Core\Persisteds\PersistedUniquesList;
use Websyspro\Entity\Enums\ScriptType;
use Websyspro\Entity\Interfaces\IPersistedColumn;
use Websyspro\Entity\Interfaces\IPersistedForeignKeys;
use Websyspro\Entity\Interfaces\IPersistedGeneration;
use Websyspro\Entity\Interfaces\IPersistedOneToOnes;
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
  public DataList $persistedOneToOnes;

  public function __construct(
    public DataList $entitys,
    public string $module
  ){}

  public function getDatabase(
  ): void {
    $this->connect = (
      Connect::set(
        lcfirst(
          Util::className(
            $this->module
          )
        )
      )
    );
  }

  private function getDecorationEntitys(
  ): void {
    if($this->entitys->count() !== 0){
      $this->structureTable = $this->entitys;
      $this->structureTable->mapper(
        fn(string $entity) => new StructureTable($entity)
      );
    }
  }

  private function get(
    string $query
  ): DataList {
    return $this->connect->query($query);
  }

  private function setPersistedsColumns(
  ): DataList {
    return (
      $this->get(
        MySqlScript::columns(
          $this->connect->database()
        )
      )->mapper(fn(object $obj) => (
        new IPersistedColumn(
          ...(array)$obj
        )
      ))
    );
  }

  private function setPersistedsRequireds(
  ): DataList {
    return $this->get(
      MySqlScript::requireds(
        $this->connect->database()
      )
    )->mapper(fn(object $obj) => (
      new IPersistedRequireds(
        ...(array)$obj
      )
    ));
  }  

  private function setPersistedsPrimaryKeys(
  ): DataList {
    return $this->get(
      MySqlScript::primaryKeys(
        $this->connect->database()
      )
    )->mapper(fn(object $obj) => (
      new IPersistedPrimaryKey(
        ...(array)$obj
      )
    ));
  }
  
  private function setPersistedsGenerations(
  ): DataList {
    return $this->get(
      MySqlScript::generations(
        $this->connect->database()
      )
    )->mapper(fn(object $obj) => (
      new IPersistedGeneration(
        ...(array)$obj
      )
    ));
  }
  
  private function setPersistedsUniques(
  ): DataList {
    return $this->get(
      MySqlScript::uniques(
        $this->connect->database()
      )
    )->mapper(fn(object $obj) => (
      new IPersistedUnique(
        ...(array)$obj
      )
    ));
  }
  
  private function setPersistedsStatistics(
  ): DataList {
    return $this->get(
      MySqlScript::statistics(
        $this->connect->database()
      )
    )->mapper(fn(object $obj) => (
      new IPersistedStatistics(
        ...(array)$obj
      )
    ));
  }

  private function setPersistedsForeignKeys(
  ): DataList {
    return $this->get(
      MySqlScript::foreignKeys(
        $this->connect->database()
      )
    )->mapper(fn(object $obj) => (
      new IPersistedForeignKeys(
        ...(array)$obj
      )
    ));
  }
  
  private function getPersistedsEntitys(
  ): void {
    $this->persistedColumn = $this->setPersistedsColumns();
    $this->persistedRequireds = $this->setPersistedsRequireds();
    $this->persistedPrimaryKeys = $this->setPersistedsPrimaryKeys();
    $this->persistedGenerations = $this->setPersistedsGenerations();
    $this->persistedUniques = $this->setPersistedsUniques();
    $this->persistedStatistics = $this->setPersistedsStatistics();
    $this->persistedForeignKeys = $this->setPersistedsForeignKeys();
  }

  private function getPersistedColumns(
    StructureTable $structureTable
  ): PersistedColumnsList {
    if(isset($this->persistedColumn) === false){
      return new PersistedColumnsList(
        DataList::create()
      );
    }

    return new PersistedColumnsList(
      $this->persistedColumn->copy()->where(
        fn(IPersistedColumn $persistedColumn) => (
          $persistedColumn->table === $structureTable->table
        )
      )
    );
  }

  private function getPersistedRequireds(
    StructureTable $structureTable
  ): PersistedRequiredsList {
    return new PersistedRequiredsList( 
      $this->persistedRequireds->copy()->where(
        fn(IPersistedRequireds $persistedRequireds) => (
          $persistedRequireds->table === $structureTable->table
        )
      )
    );
  }
  
  private function getPersistedPrimaryKeys(
    StructureTable $structureTable
  ): PersistedPrimaryKeysList {
    if(isset($this->persistedPrimaryKeys) === false){
      return new PersistedPrimaryKeysList(
        DataList::create()
      );
    }

    return new PersistedPrimaryKeysList(
      $this->persistedPrimaryKeys->copy()->where(
        fn(IPersistedPrimaryKey $persistedPrimaryKey) => (
          $persistedPrimaryKey->table === $structureTable->table
        )
      )
    );
  }

  private function getPersistedGenerations(
    StructureTable $structureTable
  ): PersistedGenerationsList {
    if(isset($this->persistedGenerations) === false){
      return new PersistedGenerationsList(
        DataList::create()
      );
    }

    return new PersistedGenerationsList(
      $this->persistedGenerations->copy()->where(
        fn(IPersistedGeneration $persistedGeneration) => (
          $persistedGeneration->table === $structureTable->table
        )
      )
    );
  }

  private function getPersistedUniques(
    StructureTable $structureTable
  ): PersistedUniquesList {
    if(isset($this->persistedUniques) === false){
      return new PersistedUniquesList(
        DataList::create()
      );
    }

    return new PersistedUniquesList(
      $this->persistedUniques->copy()->where(
        fn(IPersistedUnique $persistedUnique) => (
          $persistedUnique->table === $structureTable->table
        )
      )
    );
  }

  private function getPersistedStatistics(
    StructureTable $structureTable
  ): PersistedStatisticsList {
    if(isset($this->persistedStatistics) === false){
      return new PersistedStatisticsList(
        DataList::create()
      );
    }

    return new PersistedStatisticsList(
      $this->persistedStatistics->copy()->where(
        fn(IPersistedStatistics $persistedStatistic) => (
          $persistedStatistic->table === $structureTable->table
        )
      )
    );
  }

  private function getPersistedForeignKeys(
    StructureTable $structureTable
  ): PersistedForeignKeysList {
    if(isset($this->persistedForeignKeys) === false){
      return new PersistedForeignKeysList(
        DataList::create()
      );
    }

    return new PersistedForeignKeysList(
      $this->persistedForeignKeys->copy()->where(
        fn(IPersistedForeignKeys $persistedForeignKey) => (
          $persistedForeignKey->table === $structureTable->table
        )
      )
    );
  }
  
  private function getPersistedOneToOnes(
    StructureTable $structureTable
  ): PersistedOneToOnesList {
    if(isset($this->persistedOneToOnes) === false){
      return new PersistedOneToOnesList(
        DataList::create()
      );
    }

    return new PersistedOneToOnesList(
      $this->persistedOneToOnes->copy()->where(
        fn(IPersistedOneToOnes $persistedOneToOnes) => (
          $persistedOneToOnes->table === $structureTable->table
        )
      )
    );
  }  

  private function addUpdateScripts(
    DataList $updateScripts
  ): void {
    if(isset($this->updateScripts) === false){
      $this->updateScripts = (
        DataList::create()
      );
    }
    
    $updateScripts->forEach(
      fn(IUpdateScript $updateScripts) => (
        $this->updateScripts->add(
          $updateScripts
        )
      )
    );
  }

  private function getUpdateStructureColumns(
    StructureTable $structureTable
  ): void {
    $this->addUpdateScripts(
      (new MySqlUpdateColumns(
        $this->getPersistedColumns($structureTable),
        $this->getPersistedRequireds($structureTable), $structureTable, $this->connect
      ))->startUpdates()->updateScripts()
    );
  }

  private function getUpdateStructurePrimaryKeys(
    StructureTable $structureTable
  ): void {
    $this->addUpdateScripts(
      (new MySqlUpdatePrimaryKeys(
        $this->getPersistedPrimaryKeys($structureTable), $structureTable
      ))->startUpdates()->updateScripts()
    );
  }

  private function getUpdateStructureGenerations(
    StructureTable $structureTable
  ): void {
    $this->addUpdateScripts(
      (new MySqlUpdateGenerations(
        $this->getPersistedGenerations($structureTable), $structureTable
      ))->startUpdates()->updateScripts()
    );
  }

  private function getUpdateStructureUniques(
    StructureTable $structureTable
  ): void {
    $this->addUpdateScripts(
      (new MySqlUpdateUniques(
        $this->getPersistedUniques($structureTable), $structureTable
      ))->startUpdates()->updateScripts()
    );
  }

  private function getUpdateStructureStatistics(
    StructureTable $structureTable
  ): void {
    $this->addUpdateScripts(
      (new MySqlUpdateStatistics(
        $this->getPersistedStatistics($structureTable), $structureTable
      ))->startUpdates()->updateScripts()
    );
  }

  private function getUpdateStructureForeignKeys(
    StructureTable $structureTable
  ): void {
    $this->addUpdateScripts(
      (new MySqlUpdateForeignKeys(
        $this->getPersistedForeignKeys($structureTable), $structureTable
      ))->startUpdates()->updateScripts()
    );
  }
  
  private function getUpdateEntitys(
  ): void {
    $this->structureTable->forEach(
      function(StructureTable $structureTable){
        $this->getUpdateStructureColumns($structureTable);
        $this->getUpdateStructurePrimaryKeys($structureTable);
        $this->getUpdateStructureGenerations($structureTable);
        $this->getUpdateStructureUniques($structureTable);
        $this->getUpdateStructureStatistics($structureTable);
        $this->getUpdateStructureForeignKeys($structureTable);
      }
    );
  }

  private function executeDatabase(
    IUpdateScript $updateScript
  ): void {
    if($this->connect->exec($updateScript->sql) === true){
      Message::infors(LogType::database, $updateScript->message);
    }
  }

  private function SetUpdateDatabase(
  ): void {
    $updateScriptsNotDependence = $this->updateScripts->copy()->where(
      fn(IUpdateScript $updateScript) => (
        $updateScript->scriptType === ScriptType::notDependence
      )
    );

    $updateScriptsDependence = $this->updateScripts->copy()->where(
      fn(IUpdateScript $updateScript) => (
        $updateScript->scriptType === ScriptType::dependence
      )
    );    

    $updateScriptsNotDependence->forEach(fn(IUpdateScript $updateScript) => $this->executeDatabase($updateScript));
    $updateScriptsDependence->forEach(fn(IUpdateScript $updateScript) => $this->executeDatabase($updateScript));
  }
  
  public function update(
  ): void {
    $module = new $this->module;
    if($module->isUpdate === true){
      $this->getDatabase();
      $this->getDecorationEntitys();
      $this->getPersistedsEntitys();

      if(isset($this->structureTable)){
        $this->getUpdateEntitys();
        $this->setUpdateDatabase();
      }
    }
  }
}