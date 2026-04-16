<?php

namespace Websyspro\Entity\Interfaces;

class Column
{
  public function __construct(
    public string $name,
    public string $alias
  ){}
}