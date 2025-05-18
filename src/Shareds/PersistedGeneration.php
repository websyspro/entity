<?php

namespace Websyspro\Entity\Shareds;

class PersistedGeneration
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}