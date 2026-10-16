<?php

namespace Websyspro\Entity\Types;

use Closure;

class ColumnList 
extends ColumnAbstract
{
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
  
  public function leftJoin(
    Closure $closure
  ): void {}

  public function innerJoin(
    Closure $closure
  ): void {}
  
  public function rightJoin(
    Closure $closure
  ): void {}  
}
