<?php

namespace Websyspro\Entity\Interfaces;

class IPersistedGeneration
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}