<?php

namespace Websyspro\Entity\Shareds;

class PersistedUnique
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}