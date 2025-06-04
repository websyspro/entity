<?php

namespace Websyspro\Entity\Interfaces;

class IColumnType
{
  public function __construct(
    public string $name,
    public string $type
  ){}
}