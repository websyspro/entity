<?php

namespace Websyspro\Entity\Decorators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class HasOneToOne
{
  public function __construct(
    public readonly string $entity
  ) {}
}
