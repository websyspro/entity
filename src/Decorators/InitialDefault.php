<?php

namespace Websyspro\Entity\Decorators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class InitialDefault
{
  public function __construct(
    public readonly string $generator = ''
  ){}
}
