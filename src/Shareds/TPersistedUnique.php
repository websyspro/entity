<?php

namespace Websyspro\Entity\Shareds;

class TPersistedUnique
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}