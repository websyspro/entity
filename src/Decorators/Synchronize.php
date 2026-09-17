<?php

namespace Websyspro\Entity\Decorators;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Synchronize
{
  public function __construct(
    public readonly bool $enable = true
  ){}
}
