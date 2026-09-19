<?php

namespace Websyspro\Entity\Interfaces;

class ColumnType
{
  public function __construct(
    public readonly string $name,
    public readonly string $type,
    public readonly string $required
  ){}
}