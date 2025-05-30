<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\Collection;

class StructureTable
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
      new Collection(explode( "\\", $this->class))
    )->Slice(-1)->Mapper(
      fn(string $str) => preg_replace("/Entity$/", "", $str)
    )->First();
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
    return new StructureTableStatistics(
      $this->class
    );
  }

  public function ForeignKeys(
  ): StructureTableForeignKeys {
    return new StructureTableForeignKeys(
      $this->class
    );
  }
}