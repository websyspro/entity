<?php

namespace Websyspro\Entity\Interfaces;

class IPersistedForeignKeys
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}