<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\TList;

class TStructureTable
{ 
  public string $table;

  public function __construct(
    public string $class
  ){
    $this->EntityParse();
  }

  private function EntityParse(
  ): void {
    $this->table = (
      new TList(explode( "\\", $this->class))
    )->Slice(-1)->Mapper(
      fn(string $str) => preg_replace("/Entity$/", "", $str)
    )->First();
  }

  public function Columns(
  ): TStructureTableColumns {
    return new TStructureTableColumns($this->class);
  }

  public function Requireds(
  ): TStructureTableRequireds {
    return new TStructureTableRequireds($this->class);
  }  

  public function PrimaryKeys(
  ): TStructureTablePrimaryKeys {
    return new TStructureTablePrimaryKeys($this->class);
  }

  public function Generations(
  ): TStructureTableGenerations {
    return new TStructureTableGenerations($this->class);
  }

  public function Uniques(
  ): TStructureTableUniques {
    return new TStructureTableUniques($this->class);
  }
  
  public function Statistics(
  ): TStructureTableStatistics {
    return new TStructureTableStatistics($this->class);
  }

  public function ForeignKeys(
  ): TStructureTableForeignKeys {
    return new TStructureTableForeignKeys($this->class);
  }
}