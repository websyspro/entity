<?php

namespace Websyspro\Entity\Shareds;

class PersistedStatistics
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}