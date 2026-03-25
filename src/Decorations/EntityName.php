<?php

namespace Websyspro\Entity\Decorations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class EntityName
{
  public function __construct(
    public string $entityName
  ){}
}