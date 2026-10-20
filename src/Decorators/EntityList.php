<?php

namespace Websyspro\Entity\Decorators;

use Attribute;
use Closure;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EntityList
{
  public function __construct(
    public readonly string $entity
  ){}

  public function any(
    Closure $arrow
  ): void {}

  public function all(
    Closure $arrow
  ): void {}

  public function none(
    Closure $arrow
  ): void {}

  public function where(
    Closure $closure
  ): void {}

  public function select(
    Closure $closure
  ): void {}  
  
  public function include(
    mixed ...$closure
  ): void {}  
}
