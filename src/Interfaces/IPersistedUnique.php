<?php

namespace Websyspro\Entity\Interfaces;

class IPersistedUnique
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}