<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Commons\Statics;

class StructureTable
{ 
  public string $table;
  public string $module;

  public function __construct(
    public string $entity
  ){
    $this->entityModule();
    $this->entityParse();
  }

  private function entityModule(
  ): void {
    if(isset(Statics::$modules)){
      $this->module = Statics::$modules->copy()->where(
        fn(mixed $itemModule) => $itemModule->entity === $this->entity
      )->first()->module;
    }
  } 

  private function entityParse(
  ): void {
    $this->table = (
      new DataList(explode( "\\", $this->entity))
    )->slice(-1)->mapper(fn(string $str) => preg_replace("/Entity$/", "", $str))->first();
  }

  public function columns(
  ): StructureTableColumns {
    return new StructureTableColumns($this->entity);
  }

  public function requireds(
  ): StructureTableRequireds {
    return new StructureTableRequireds($this->entity);
  }  

  public function primaryKeys(
  ): StructureTablePrimaryKeys {
    return new StructureTablePrimaryKeys($this->entity);
  }

  public function generations(
  ): StructureTableGenerations {
    return new StructureTableGenerations($this->entity);
  }

  public function uniques(
  ): StructureTableUniques {
    return new StructureTableUniques($this->entity);
  }
  
  public function statistics(
  ): StructureTableStatistics {
    return new StructureTableStatistics($this->entity);
  }

  public function foreignKeys(
  ): StructureTableForeignKeys {
    return new StructureTableForeignKeys($this->entity);
  }

  public function oneToOnes(
  ): StructureTableOneToOnes {
    return new StructureTableOneToOnes($this->entity);
  }

  public function oneToManys(
  ): StructureTableOneToManys {
    return new StructureTableOneToManys($this->entity);
  }  

  public function eventInserts(
  ): StructureTableEventInserts {
    return new StructureTableEventInserts($this->entity);
  }

  public function eventUpdates(
  ): StructureTableEventUpdates {
    return new StructureTableEventUpdates($this->entity);
  }

  public function eventDeletes(
  ): StructureTableEventDeletes {
    return new StructureTableEventDeletes($this->entity);
  }  
}