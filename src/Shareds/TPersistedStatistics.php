<?php

namespace Websyspro\Entity\Shareds;

class TPersistedStatistics
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}