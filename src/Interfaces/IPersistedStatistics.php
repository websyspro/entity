<?php

namespace Websyspro\Entity\Interfaces;

class IPersistedStatistics
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}