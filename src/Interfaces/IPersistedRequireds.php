<?php

namespace Websyspro\Entity\Interfaces;

class IPersistedRequireds
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}