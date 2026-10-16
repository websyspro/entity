<?php

namespace Websyspro\Entity\Decorators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class HasOneToMany
{
  public function __construct(
    public readonly string $entity
  ){}
}
