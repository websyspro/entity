<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\TList;
use Websyspro\Commons\TReflect;
use Websyspro\Database\TConnect;
use Websyspro\Entity\Core\Designs\MySql\TMySqlUpdateColumn;
use Websyspro\Entity\Core\Persisteds\MySqlScript;
use Websyspro\Entity\Shareds\PersistedColumn;
use Websyspro\Entity\Shareds\PersistedForeignKeys;
use Websyspro\Entity\Shareds\PersistedGeneration;
use Websyspro\Entity\Shareds\PersistedPrimaryKey;
use Websyspro\Entity\Shareds\PersistedRequireds;
use Websyspro\Entity\Shareds\PersistedStatistics;
use Websyspro\Entity\Shareds\PersistedUnique;

class StructureDatabase
{
  public string $database;

  public TList $structureTable;
  public TList $persistedColumn;
  public TList $persistedPrimaryKeys;
  public TList $persistedGenerations;
  public TList $persistedRequireds;
  public TList $persistedUniques;
  public TList $persistedStatistics;
  public TList $persistedForeignKeys;

  public function __construct(
    public string $class
  ){}

  private function GetDatabase(
  ): void {
    $this->database = (
      new TList(preg_split(
        "/(?=[A-Z])/", $this->class
      ))
    )->Find(fn(string $path) => empty($path) === false
    )->Slice(0, preg_match("/Database$/", $this->class) === 1 ? -1 : null
    )->JoinNotSpace();
  }

  private function GetDecorationEntitys(
  ): void {
    $reflectClass = (
      TReflect::Class(
        $this->class
      )
    );

    [$attributeEntites] = $reflectClass->getAttributes();
    if($attributeEntites !== null){
      $this->structureTable = new TList(
        $attributeEntites->newInstance()->entitys
      );

      $this->structureTable->Mapper(
        fn(string $class) => new StructureTable($class)
      );
    }
  }

  private function Get(
    string $query
  ): TList {
    return (new TConnect())->Get($query)->All();
  }

  private function SetPersistedsColumns(
  ): TList {
    return $this->Get(
      MySqlScript::Columns()
    )->Mapper(fn(object $obj) => (
      new PersistedColumn(
        ...(array)$obj
      )
    ));
  }

  private function SetPersistedsPrimaryKeys(
  ): TList {
    return $this->Get(
      MySqlScript::PrimaryKeys()
    )->Mapper(fn(object $obj) => (
      new PersistedPrimaryKey(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsGenerations(
  ): TList {
    return $this->Get(
      MySqlScript::Generations()
    )->Mapper(fn(object $obj) => (
      new PersistedGeneration(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsRequireds(
  ): TList {
    return $this->Get(
      MySqlScript::Requireds()
    )->Mapper(fn(object $obj) => (
      new PersistedRequireds(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsUniques(
  ): TList {
    return $this->Get(
      MySqlScript::Uniques()
    )->Mapper(fn(object $obj) => (
      new PersistedUnique(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsStatistics(
  ): TList {
    return $this->Get(
      MySqlScript::Statistics()
    )->Mapper(fn(object $obj) => (
      new PersistedStatistics(
        ...(array)$obj
      )
    ));
  }

  private function SetPersistedsForeignKeys(
  ): TList {
    return $this->Get(
      MySqlScript::ForeignKeys()
    )->Mapper(fn(object $obj) => (
      new PersistedForeignKeys(
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
  ): TList {
    return $this->persistedColumn->Copy()->Find(
      fn(PersistedColumn $persistedColumn) => (
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
    $mySqlUpdateColumn = ( new TMySqlUpdateColumn(
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