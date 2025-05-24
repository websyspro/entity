<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Reflect;
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

  public Collection $structureTable;
  public Collection $persistedColumn;
  public Collection $persistedPrimaryKeys;
  public Collection $persistedGenerations;
  public Collection $persistedRequireds;
  public Collection $persistedUniques;
  public Collection $persistedStatistics;
  public Collection $persistedForeignKeys;

  public function __construct(
    public string $class
  ){}

  private function GetDatabase(
  ): void {
    $this->database = (
      new Collection(preg_split(
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
      $this->structureTable = new Collection(
        $attributeEntites->newInstance()->entitys
      );

      $this->structureTable->Mapper(
        fn(string $class) => new StructureTable($class)
      );
    }
  }

  private function Get(
    string $query
  ): Collection {
    return (new TConnect())->Get($query)->All();
  }

  private function SetPersistedsColumns(
  ): Collection {
    return $this->Get(
      MySqlScript::Columns()
    )->Mapper(fn(object $obj) => (
      new PersistedColumn(
        ...(array)$obj
      )
    ));
  }

  private function SetPersistedsPrimaryKeys(
  ): Collection {
    return $this->Get(
      MySqlScript::PrimaryKeys()
    )->Mapper(fn(object $obj) => (
      new PersistedPrimaryKey(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsGenerations(
  ): Collection {
    return $this->Get(
      MySqlScript::Generations()
    )->Mapper(fn(object $obj) => (
      new PersistedGeneration(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsRequireds(
  ): Collection {
    return $this->Get(
      MySqlScript::Requireds()
    )->Mapper(fn(object $obj) => (
      new PersistedRequireds(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsUniques(
  ): Collection {
    return $this->Get(
      MySqlScript::Uniques()
    )->Mapper(fn(object $obj) => (
      new PersistedUnique(
        ...(array)$obj
      )
    ));
  }
  
  private function SetPersistedsStatistics(
  ): Collection {
    return $this->Get(
      MySqlScript::Statistics()
    )->Mapper(fn(object $obj) => (
      new PersistedStatistics(
        ...(array)$obj
      )
    ));
  }

  private function SetPersistedsForeignKeys(
  ): Collection {
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
  ): Collection {
    return $this->persistedColumn->Copy()->Where(
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