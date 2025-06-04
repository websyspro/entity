<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Commons\Reflect;
use Websyspro\Entity\Core\Designs\MySql\MySqlUpdateColumn;
use Websyspro\Entity\Core\Persisteds\MySqlScript;
use Websyspro\Entity\Interfaces\IPersistedColumn;
use Websyspro\Entity\Interfaces\IPersistedForeignKeys;
use Websyspro\Entity\Interfaces\IPersistedGeneration;
use Websyspro\Entity\Interfaces\IPersistedPrimaryKey;
use Websyspro\Entity\Interfaces\IPersistedRequireds;
use Websyspro\Entity\Interfaces\IPersistedStatistics;
use Websyspro\Entity\Interfaces\IPersistedUnique;

class StructureDatabase
{
  public string $database;

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

  private function GetDatabase(
  ): void {
    $this->database = (
      DataList::Create(preg_split(
        "/(?=[A-Z])/", $this->class
      ))
    )->Where(fn(string $path) => empty($path) === false
    )->Slice(0, preg_match("/Database$/", $this->class) === 1 ? -1 : null
    )->JoinNotSpace();
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
    // return (new TConnect())->Get($query)->All();
    return DataList::Create([]);
  }

  private function SetPersistedsColumns(
  ): DataList {
    return $this->Get(
      MySqlScript::Columns()
    )->Mapper(fn(object $obj) => (
      new IPersistedColumn(
        ...(array)$obj
      )
    ));
  }

  private function SetPersistedsPrimaryKeys(
  ): DataList {
    return $this->Get(
      MySqlScript::PrimaryKeys()
    )->Mapper(fn(object $obj) => (
      new IPersistedPrimaryKey(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsGenerations(
  ): DataList {
    return $this->Get(
      MySqlScript::Generations()
    )->Mapper(fn(object $obj) => (
      new IPersistedGeneration(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsRequireds(
  ): DataList {
    return $this->Get(
      MySqlScript::Requireds()
    )->Mapper(fn(object $obj) => (
      new IPersistedRequireds(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsUniques(
  ): DataList {
    return $this->Get(
      MySqlScript::Uniques()
    )->Mapper(fn(object $obj) => (
      new IPersistedUnique(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsStatistics(
  ): DataList {
    return $this->Get(
      MySqlScript::Statistics()
    )->Mapper(fn(object $obj) => (
      new IPersistedStatistics(
        ...(array)$obj
      )
    ));
  }

  private function SetPersistedsForeignKeys(
  ): DataList {
    return $this->Get(
      MySqlScript::ForeignKeys()
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
      $this->persistedForeignKeys = $this->SetPersistedsPrimaryKeys();
      $this->persistedGenerations = $this->SetPersistedsGenerations();
      $this->persistedRequireds = $this->SetPersistedsRequireds();
      $this->persistedUniques = $this->SetPersistedsUniques();
      $this->persistedStatistics = $this->SetPersistedsStatistics();
      $this->persistedForeignKeys = $this->SetPersistedsForeignKeys();
    }
  }

  private function GetPersistedColumnsFromTable(
    StructureTable $structureTable
  ): DataList {
    return $this->persistedColumn->Copy()->Where(
      fn(IPersistedColumn $persistedColumn) => (
        $persistedColumn->table === $structureTable->table
      )
    );
  }

  private function GetUpdateStructureColumnsEntitys(
    StructureTable $structureTable
  ): void {
    $this->GetPersistedColumnsFromTable($structureTable);
    print_r($structureTable->Columns()->ListType());
  }

  private function GetUpdateStructureEntitys(
    StructureTable $structureTable
  ): void {
    $mySqlUpdateColumn = ( new MySqlUpdateColumn(
      $this->GetPersistedColumnsFromTable($structureTable), $structureTable
    ))->StartUpdates();
  }

  private function GetUpdateEntitys(
  ): void {
    $this->structureTable->ForEach(
      fn(StructureTable $structureTable) => (
        $this->GetUpdateStructureEntitys(
          $structureTable
        )
      )
    );
  }
  
  public function Update(
  ): void {
    $this->GetDatabase();
    $this->GetDecorationEntitys();
    $this->GetPersistedsEntitys();
    $this->GetUpdateEntitys();
  }
}