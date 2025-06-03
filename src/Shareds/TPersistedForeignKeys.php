<?php

namespace Websyspro\Entity\Shareds;

class TPersistedForeignKeys
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}