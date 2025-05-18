<?php

namespace Websyspro\Entity\Shareds;

class ColumnType
{
  public function __construct(
    public string $name,
    public string $type
  ){}
}