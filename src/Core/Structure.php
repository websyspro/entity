<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\TList;

class Structure
{
  public function __construct(
    public string $entity
  ){}

  public function Columns(
  ): StructureColumns {
    return new StructureColumns(
      $this->entity
    );
  }
}