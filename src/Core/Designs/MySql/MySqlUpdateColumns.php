<?php

namespace Websyspro\Entity\Core\Designs\MySql;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\StructureTable;
use Websyspro\Entity\Enums\ScriptType;
use Websyspro\Entity\Interfaces\IColumnType;
use Websyspro\Entity\Interfaces\IPersistedColumn;
use Websyspro\Entity\Interfaces\IPersistedRequireds;
use Websyspro\Entity\Interfaces\IUpdateScript;

class MySqlUpdateColumns
{
  public DataList $updateScripts;

  public function __construct(
    public DataList $persistedColumns,
    public DataList $persistedRequireds,
    public StructureTable $structureTable
  ){}

  public function SetInicial(
  ): void {
    $this->updateScripts = DataList::Create();
  }

  private function IsColumnExistInPersisted(
    string $name
  ): bool {
    $persisteedColumn = $this->persistedColumns->Copy()->Where(
      fn(IPersistedColumn $persistedColumn) => $persistedColumn->name === $name
    );

    return $persisteedColumn->Exist();
  }

  private function IsColumnNotEqualTypeWithPersisted(
    string $name
  ): bool {
    $persistedColumn = $this->persistedColumns->Copy()->Where(
      fn(IPersistedColumn $p) => $p->name === $name
    );

    return $this->structureTable->Columns()->Type($name) === $persistedColumn->First()->type;
  }  

  private function IsColumnNotEqualRequiredWithPersisted(
    string $name
  ): bool {
    $persistedRequiredColumn = $this->persistedRequireds->Copy()->Where(
      fn(IPersistedRequireds $persistedRequireds) => $persistedRequireds->name === $name
    );

    return $this->structureTable->Requireds()->IsRequired($name) === $persistedRequiredColumn->Exist();
  }

  private function SetCreateds(
  ): void {
    if($this->persistedColumns->Exist() === false){
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
    if($this->persistedColumns->Exist() === true){
      $columnsAdd = $this->structureTable->Columns()->ListType()->Where(
        fn(IColumnType $columnType) => $this->IsColumnExistInPersisted($columnType->name) === false
      );

      if($columnsAdd->Exist() === true){
        $columnsAdd->ForEach(fn(IColumnType $columnType) => (
          $this->updateScripts->Add(
            new IUpdateScript(
              "alter table {$this->structureTable->table} add column {$columnType->name} {$columnType->type} {$this->structureTable->Requireds()->Sql($columnType->name)} {$this->structureTable->Columns()->Before($columnType->name)}",
              "Column {$columnType->name} added with successfully to {$this->structureTable->table}", ScriptType::NotDependence
            )
          )
        ));
      }
    }
  }  

  private function SetModify(
  ): void {
    if($this->persistedColumns->Exist() === true){
      $columnsModify = $this->structureTable->Columns()->ListType()->Where(
        fn(IColumnType $columnType) => (
          $this->IsColumnExistInPersisted($columnType->name) === true && (
            $this->IsColumnNotEqualTypeWithPersisted($columnType->name) === false || 
            $this->IsColumnNotEqualRequiredWithPersisted($columnType->name) === false
          )
        )
      );

      if($columnsModify->Exist()){
        $columnsModify->ForEach(fn(IColumnType $columnType) => (
          $this->updateScripts->Add(
            new IUpdateScript(
              "alter table {$this->structureTable->table} modify column {$columnType->name} {$columnType->type} {$this->structureTable->Requireds()->Sql($columnType->name)}",
              "Column {$columnType->name} modify with successfully to {$this->structureTable->table}", ScriptType::NotDependence
            )
          )
        ));
      }
    }
  }
  
  private function SetDrops(
  ): void {
    if($this->persistedColumns->Exist() === true){
      $persistedColumns = $this->persistedColumns->Copy()->Where(
        fn(IPersistedColumn $persistedColumn) => (
          $this->structureTable->Columns()->ColumnExist($persistedColumn->name) 
        ) === false
      );

      if($persistedColumns->Exist() === true){
        $persistedColumns->ForEach(fn(IPersistedColumn $persistedColumn) => (
          $this->updateScripts->Add(
            new IUpdateScript(
              "alter table {$this->structureTable->table} drop {$persistedColumn->name}",
              "Column {$persistedColumn->name} drop with successfully to {$this->structureTable->table}", ScriptType::NotDependence
            )
          )          
        ));
      }
    }
  }
  
  public function StartUpdates(
  ): MySqlUpdateColumns {
    $this->SetInicial();
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