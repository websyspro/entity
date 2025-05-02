<?php

namespace Websyspro\Entity\Core;

class StructureTable
{
  public function __construct(
    public string $entity
  ){}

  public function Columns(
  ): StructureTableColumns {
    return new StructureTableColumns(
      $this->entity
    );
  }

  public function PrimaryKeys(): void {}

  public function Generations(): void {}

  public function Uniques(): void {}
  
  public function Statistics(): void {}

  public function ForeignKeys(): void {}

  public function UpdateStructures(
  ): void{}
}