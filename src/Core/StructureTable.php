<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Commons\Statics;

class StructureTable
{ 
  public string $table;
  public string $module;

  public function __construct(
    public string $class
  ){
    $this->EntityModule();
    $this->EntityParse();
  }

  private function EntityModule(
  ): void {
    $this->module = Statics::$modules->Where(
      fn(mixed $itemModule) => $itemModule->entity === $this->class
    )->First()->module;
  } 

  private function EntityParse(
  ): void {
    $this->table = (
      new DataList(explode( "\\", $this->class))
    )->Slice(-1)->Mapper(fn(string $str) => preg_replace("/Entity$/", "", $str))->First();
  }

  public function Columns(
  ): StructureTableColumns {
    return new StructureTableColumns($this->class);
  }

  public function Requireds(
  ): StructureTableRequireds {
    return new StructureTableRequireds($this->class);
  }  

  public function PrimaryKeys(
  ): StructureTablePrimaryKeys {
    return new StructureTablePrimaryKeys($this->class);
  }

  public function Generations(
  ): StructureTableGenerations {
    return new StructureTableGenerations($this->class);
  }

  public function Uniques(
  ): StructureTableUniques {
    return new StructureTableUniques($this->class);
  }
  
  public function Statistics(
  ): StructureTableStatistics {
    return new StructureTableStatistics($this->class);
  }

  public function ForeignKeys(
  ): StructureTableForeignKeys {
    return new StructureTableForeignKeys($this->class);
  }

  public function EventInserts(
  ): StructureTableEventInserts {
    return new StructureTableEventInserts($this->class);
  }

  public function EventUpdates(
  ): StructureTableEventUpdates {
    return new StructureTableEventUpdates($this->class);
  }

  public function EventDeletes(
  ): StructureTableEventDeletes {
    return new StructureTableEventDeletes($this->class);
  }  
}