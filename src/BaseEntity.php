<?php

namespace Websyspro\Entity;

use ReflectionClass;
use Websyspro\Entity\Types\ColumnList;

abstract class BaseEntity {
  private static array $orderCache = [];

  public function as(
    string $alias    
  ): void {}

  public function sum(
    mixed $expr
  ): void {}

  public function include(
    mixed ...$closure
  ): void {}
}
