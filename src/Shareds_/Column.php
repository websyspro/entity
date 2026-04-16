<?php

namespace Websyspro\Entity\Shareds_;

class Column
{
  public function __construct(
    public string $name,
    public string $columnType,
    public object|null $instance = null
  ){}
}