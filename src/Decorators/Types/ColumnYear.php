<?php

namespace Websyspro\Entity\Decorators\Types;

class ColumnYear 
extends ColumnType
{
  public function equals(
    string $datetime
  ): void {}

  public function before(
    string $datetime
  ): void {}

  public function after(
    string $datetime
  ): void {}

  public function between(
    string $start, 
    string $end
  ): void {}

  public function toDate(
    string $datetime
  ): void {}
}
