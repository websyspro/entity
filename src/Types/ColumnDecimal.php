<?php

namespace Websyspro\Entity\Types;

class ColumnDecimal 
extends ColumnAbstract
{
  public function equals(
    int|float $value
  ): void {}

  public function greaterThan(
    int|float $value
  ): void {}

  public function lessThan(
    int|float $value
  ): void {}

  public function between(
    int|float $min,
    int|float $max
  ): void {}

  public function round(
    int $decimals
  ): void {}
  
  public function floor(
    int $decimals
  ): void {}
  
  public function ceil(
    int $decimals
  ): void {}   
}
