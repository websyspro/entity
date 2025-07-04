<?php

namespace Websyspro\Entity\Core\Designs\MySql;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\Persisteds\PersistedStatisticsList;
use Websyspro\Entity\Core\StructureTable;
use Websyspro\Entity\Enums\ScriptType;
use Websyspro\Entity\Interfaces\IStatisticsNamesItem;
use Websyspro\Entity\Interfaces\IUpdateScript;

class MySqlUpdateStatistics
{
  public DataList $updateScripts;

  public function __construct(
    public PersistedStatisticsList $persistedStatisticsList,
    public StructureTable $structureTable
  ){}

  public function setInicial(
  ): void {
    $this->updateScripts = DataList::create();
  }

  public function setAdd(
  ): void {
    if($this->persistedStatisticsList->listNames()->exist() === false){
      if($this->structureTable->statistics()->listNames()->exist() === true){
        $this->structureTable->statistics()->listNames()->mapper(
          fn(IStatisticsNamesItem $statisticsNamesItem) => (
            $this->updateScripts->add(
              new IUpdateScript(
                "Create index {$statisticsNamesItem->name} on {$this->structureTable->table} ({$statisticsNamesItem->columns})",
                "Index {$statisticsNamesItem->name} added with successfully to {$this->structureTable->table}", ScriptType::notDependence
              )
            )
          )
        );
      }
    }
  }

  public function setModify(
  ): void {
    if($this->persistedStatisticsList->listNames()->exist() === true){
      if($this->structureTable->statistics()->listNames()->exist() === true){
        $this->structureTable->statistics()->listNames()
          ->where(
            fn(IStatisticsNamesItem $statisticsNamesItem) => (
              $this->persistedStatisticsList->isIndex(
                $statisticsNamesItem->name
              ) === false
            )
          )
          ->mapper(fn(IStatisticsNamesItem $statisticsNamesItem) => (
            $this->updateScripts->add(
              new IUpdateScript(
                "Create Index {$statisticsNamesItem->name} On {$this->structureTable->table} ({$statisticsNamesItem->columns})",
                "Index {$statisticsNamesItem->name} added with successfully to {$this->structureTable->table}", ScriptType::notDependence
              )
            )            
          ));
      }
    }
  }

  public function setDrops(
  ): void {
    if($this->persistedStatisticsList->listNames()->exist() === true){
      $this->persistedStatisticsList->listNames()
        ->where(
          fn(string $indexName) => (
            $this->structureTable->statistics()->isIndex($indexName) === false
          )
        )
        ->mapper(
          fn(string $indexName) => (
            $this->updateScripts->add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Drop Index {$indexName}",
                "Index {$indexName} drop with successfully to {$this->structureTable->table}", ScriptType::notDependence
              )
            )
          )
        );
    }
  }

  public function startUpdates(
  ): MySqlUpdateStatistics {
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