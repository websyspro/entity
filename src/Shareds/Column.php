<?php

namespace Websyspro\Entity\Shareds;

class Column
{
  public function __construct(
    public string $name,
    public string $columnType,
    public object $instance
  ){}
}