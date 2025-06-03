<?php

namespace Websyspro\Entity\Shareds;

class TPersistedPrimaryKey
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}