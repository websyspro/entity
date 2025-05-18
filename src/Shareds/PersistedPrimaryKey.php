<?php

namespace Websyspro\Entity\Shareds;

class PersistedPrimaryKey
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}