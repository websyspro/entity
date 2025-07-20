<?php

namespace Websyspro\Entity\Interfaces;

class IPersistedOneToOnes
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}