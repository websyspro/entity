<?php

namespace Websyspro\Entity\Decorators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class InitialInsertDefault
{
  public function __construct(
    public readonly mixed $generator = ''
  ){}
}
