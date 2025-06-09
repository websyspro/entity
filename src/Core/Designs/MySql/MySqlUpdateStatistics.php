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

  public function SetInicial(
  ): void {
    $this->updateScripts = DataList::Create();
  }

  public function SetAdd(
  ): void {
    if($this->persistedStatisticsList->ListNames()->Exist() === false){
      if($this->structureTable->Statistics()->ListNames()->Exist() === true){
        $this->structureTable->Statistics()->ListNames()->Mapper(
          fn(IStatisticsNamesItem $statisticsNamesItem) => (
            $this->updateScripts->Add(
              new IUpdateScript(
                "Create index {$statisticsNamesItem->name} on {$this->structureTable->table} ({$statisticsNamesItem->columns})",
                "Index {$statisticsNamesItem->name} added with successfully to {$this->structureTable->table}", ScriptType::NotDependence
              )
            )
          )
        );
      }
    }
  }

  public function SetModify(
  ): void {
    if($this->persistedStatisticsList->ListNames()->Exist() === true){
      if($this->structureTable->Statistics()->ListNames()->Exist() === true){
        $this->structureTable->Statistics()->ListNames()
          ->Where(
            fn(IStatisticsNamesItem $statisticsNamesItem) => (
              $this->persistedStatisticsList->IsIndex(
                $statisticsNamesItem->name
              ) === false
            )
          )
          ->Mapper(fn(IStatisticsNamesItem $statisticsNamesItem) => (
            $this->updateScripts->Add(
              new IUpdateScript(
                "Create Index {$statisticsNamesItem->name} On {$this->structureTable->table} ({$statisticsNamesItem->columns})",
                "Index {$statisticsNamesItem->name} added with successfully to {$this->structureTable->table}", ScriptType::NotDependence
              )
            )            
          ));
      }
    }
  }

  public function SetDrops(
  ): void {
    if($this->persistedStatisticsList->ListNames()->Exist() === true){
      $this->persistedStatisticsList->ListNames()
        ->Where(
          fn(string $indexName) => (
            $this->structureTable->Statistics()->IsIndex($indexName) === false
          )
        )
        ->Mapper(
          fn(string $indexName) => (
            $this->updateScripts->Add(
              new IUpdateScript(
                "Alter Table {$this->structureTable->table} Drop Index {$indexName}",
                "Index {$indexName} drop with successfully to {$this->structureTable->table}", ScriptType::NotDependence
              )
            )
          )
        );
    }
  }

  public function StartUpdates(
  ): MySqlUpdateStatistics {
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