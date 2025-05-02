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
}