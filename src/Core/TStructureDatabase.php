<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\TList;
use Websyspro\Commons\TReflect;
use Websyspro\Entity\Core\Designs\MySql\TMySqlUpdateColumn;
use Websyspro\Entity\Core\Persisteds\TMySqlScript;
use Websyspro\Entity\Shareds\TPersistedColumn;
use Websyspro\Entity\Shareds\TPersistedForeignKeys;
use Websyspro\Entity\Shareds\TPersistedGeneration;
use Websyspro\Entity\Shareds\TPersistedPrimaryKey;
use Websyspro\Entity\Shareds\TPersistedRequireds;
use Websyspro\Entity\Shareds\TPersistedStatistics;
use Websyspro\Entity\Shareds\TPersistedUnique;

class TStructureDatabase
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
    )->Where(fn(string $path) => empty($path) === false
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
        fn(string $class) => new TStructureTable($class)
      );
    }
  }

  private function Get(
    string $query
  ): TList {
    // return (new TConnect())->Get($query)->All();
    return TList::Create([]);
  }

  private function SetPersistedsColumns(
  ): TList {
    return $this->Get(
      TMySqlScript::Columns()
    )->Mapper(fn(object $obj) => (
      new TPersistedColumn(
        ...(array)$obj
      )
    ));
  }

  private function SetPersistedsPrimaryKeys(
  ): TList {
    return $this->Get(
      TMySqlScript::PrimaryKeys()
    )->Mapper(fn(object $obj) => (
      new TPersistedPrimaryKey(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsGenerations(
  ): TList {
    return $this->Get(
      TMySqlScript::Generations()
    )->Mapper(fn(object $obj) => (
      new TPersistedGeneration(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsRequireds(
  ): TList {
    return $this->Get(
      TMySqlScript::Requireds()
    )->Mapper(fn(object $obj) => (
      new TPersistedRequireds(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsUniques(
  ): TList {
    return $this->Get(
      TMySqlScript::Uniques()
    )->Mapper(fn(object $obj) => (
      new TPersistedUnique(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsStatistics(
  ): TList {
    return $this->Get(
      TMySqlScript::Statistics()
    )->Mapper(fn(object $obj) => (
      new TPersistedStatistics(
        ...(array)$obj
      )
    ));
  }

  private function SetPersistedsForeignKeys(
  ): TList {
    return $this->Get(
      TMySqlScript::ForeignKeys()
    )->Mapper(fn(object $obj) => (
      new TPersistedForeignKeys(
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
    TStructureTable $structureTable
  ): TList {
    return $this->persistedColumn->Copy()->Where(
      fn(TPersistedColumn $persistedColumn) => (
        $persistedColumn->table === $structureTable->table
      )
    );
  }

  private function GetUpdateStructureColumnsEntitys(
    TStructureTable $structureTable
  ): void {
    $this->GetPersistedColumnsFromTable($structureTable);
    print_r($structureTable->Columns()->ListType());
  }

  private function GetUpdateStructureEntitys(
    TStructureTable $structureTable
  ): void {
    $mySqlUpdateColumn = ( new TMySqlUpdateColumn(
      $this->GetPersistedColumnsFromTable($structureTable), $structureTable
    ))->StartUpdates();
  }

  private function GetUpdateEntitys(
  ): void {
    $this->structureTable->ForEach(
      fn(TStructureTable $structureTable) => (
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