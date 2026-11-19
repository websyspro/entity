<?php

namespace Websyspro\Entity\Types;

class ColumnDatetime 
extends ColumnAbstract
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
  ): void {}

  public function year(
  ): void {}  

  public function month(
  ): void {}  

  public function day(
  ): void {}
  
  public function dateFormat(
    string $formatted
  ): void {}  
}
