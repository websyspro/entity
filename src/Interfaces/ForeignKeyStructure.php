<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Entity\Interfaces\EntityNames;

class ForeignKeyStructure
{
  public function __construct(
    public readonly EntityNames $entity,
    public readonly string $references
  ){}
}