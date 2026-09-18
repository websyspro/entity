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
}
