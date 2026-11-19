<?php

namespace Websyspro\Entity\Types;

class ColumnFloat 
extends ColumnAbstract
{
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