<?php

namespace Websyspro\Entity\Interfaces;

class ForeignKeyItem
{
  public function __construct(
    public string $table,
    public string $key,
    public string $referenceTable,
    public string $referenceKey,
  ){}
}