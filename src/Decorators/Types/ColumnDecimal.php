<?php

namespace Websyspro\Entity\Decorators\Types;

class ColumnDecimal 
extends ColumnType
{
  public function equals(
    float $value
  ): void {}

  public function greaterThan(
    float $value
  ): void {}

  public function lessThan(
    float $value
  ): void {}

  public function between(
    float $min, 
    float $max
  ): void {}
}
