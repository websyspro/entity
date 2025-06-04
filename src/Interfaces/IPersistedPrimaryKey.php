<?php

namespace Websyspro\Entity\Interfaces;

class IPersistedPrimaryKey
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}