<?php

namespace Websyspro\Entity\Types;

class ColumnDate 
extends ColumnAbstract
{
  public function equals(
    string $date
  ): void {}

  public function before(
    string $date
  ): void {}

  public function after(
    string $date
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
