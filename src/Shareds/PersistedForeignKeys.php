<?php

namespace Websyspro\Entity\Shareds;

class PersistedForeignKeys
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}