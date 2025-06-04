<?php

namespace Websyspro\Entity\Decorations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class EntityList
{
  public function __construct(
    public array $entitys = []
  ){}
}