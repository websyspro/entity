<?php

namespace Websyspro\Entity\Core\Designs\MySql;

use Websyspro\Commons\DataList;
use Websyspro\Database\Connect;
use Websyspro\Entity\Core\Persisteds\PersistedColumnsList;
use Websyspro\Entity\Core\Persisteds\PersistedRequiredsList;
use Websyspro\Entity\Core\StructureTable;
use Websyspro\Entity\Enums\ScriptType;
use Websyspro\Entity\Interfaces\IColumnType;
use Websyspro\Entity\Interfaces\IPersistedColumn;
use Websyspro\Entity\Interfaces\IUpdateScript;

class MySqlUpdateColumns
{
  public DataList $updateScripts;

  public function __construct(
    public PersistedColumnsList $persistedColumnsList,
    public PersistedRequiredsList $persistedRequiredsList,
    public StructureTable $structureTable,
    public Connect $connect
  ){}

  public function SetStarteds(
  ): void {
    $this->updateScripts = (
      DataList::Create()
    );
  }

  private function SetCreateds(
  ): void {
    if($this->persistedColumnsList->Exist() === false){
      $columnsTypes = $this->structureTable->Columns()->ListType()->Mapper(
        fn(IColumnType $columnType) => "{$columnType->name} {$columnType->type} {$this->structureTable->Requireds()->Sql($columnType->name)}"
      );

      $this->updateScripts->Add(
        new IUpdateScript(
          "Create Table {$this->structureTable->table} ({$columnsTypes->JoinWithComma()}) engine=innodb",
          "Table {$this->structureTable->table} created with successfully", ScriptType::NotDependence
        )
      );
    }
  }

  private function SetAdd(
  ): void {
    if($this->persistedColumnsList->Exist() === true){
      $columnsAdd = $this->structureTable->Columns()->ListType()->Where(
        fn(IColumnType $columnType) => $this->persistedColumnsList->ColumnExist($columnType->name) === false
      );

      if($columnsAdd->Exist() === true){
        $columnsAdd->ForEach(fn(IColumnType $columnType) => (
          $this->updateScripts->Add(
            new IUpdateScript(
              "Alter Table {$this->structureTable->table} Add Column {$columnType->name} {$columnType->type} {$this->structureTable->Requireds()->Sql($columnType->name)} {$this->structureTable->Columns()->Before($columnType->name)}",
              "Column {$columnType->name} added with successfully to {$this->structureTable->table}", ScriptType::NotDependence
            )
          )
        ));
      }
    }
  }  

  private function SetModify(
  ): void {
    if($this->persistedColumnsList->Exist() === true){
      $columnsModify = $this->structureTable->Columns()->ListType()->Where(
        fn(IColumnType $columnType) => (
          $this->persistedColumnsList->ColumnExist($columnType->name) === true && (
            $this->structureTable->Columns()->Type($columnType->name) !== $this->persistedColumnsList->Type($columnType->name) || 
            $this->structureTable->Requireds()->IsRequired($columnType->name) !== $this->persistedRequiredsList->IsRequired($columnType->name)
          )
        )
      );

      if($columnsModify->Exist()){
        $columnsModify->ForEach(fn(IColumnType $columnType) => (
          $this->updateScripts->Add(
            new IUpdateScript(
              "Alter Table {$this->structureTable->table} Modify Column {$columnType->name} {$columnType->type} {$this->structureTable->Requireds()->Sql($columnType->name)}",
              "Column {$columnType->name} modify with successfully to {$this->structureTable->table}", ScriptType::NotDependence
            )
          )
        ));
      }
    }
  }
  
  private function SetDrops(
  ): void {
    if($this->persistedColumnsList->Exist() === true){
      $persistedColumns = $this->persistedColumnsList->Columns()->Where(
        fn(IPersistedColumn $persistedColumn) => (
          $this->structureTable->Columns()->ColumnExist($persistedColumn->name) 
        ) === false
      );

      if($persistedColumns->Exist() === true){
        $persistedColumns->ForEach(fn(IPersistedColumn $persistedColumn) => (
          $this->connect->Query(
            "Select Count(*) as IsNotNull 
               From {$this->structureTable->table} 
              Where {$persistedColumn->name} Is Not Null"
          )->ForEach(
            function(object $row) use($persistedColumn) {
              if((int)$row->IsNotNull === 0){
                $this->updateScripts->Add(
                  new IUpdateScript(
                    "Alter Table {$this->structureTable->table} Drop {$persistedColumn->name}",
                    "Column {$persistedColumn->name} drop with successfully to {$this->structureTable->table}", ScriptType::NotDependence
                  )
                );
              }
            }
          )
        ));
      }
    }
  }
  
  public function StartUpdates(
  ): MySqlUpdateColumns {
    $this->SetStarteds();
    $this->SetCreateds();
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