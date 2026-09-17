<?php

namespace Websyspro\Entity\Decorators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Unique
{
  public function __construct(
    public readonly int $group = 1
  ){}
}
