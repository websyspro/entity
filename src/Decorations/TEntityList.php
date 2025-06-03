<?php

namespace Websyspro\Entity\Decorations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class TEntityList
{
  public function __construct(
    public array $entitys = []
  ){}
}