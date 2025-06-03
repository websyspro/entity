<?php

namespace Websyspro\Entity\Shareds;

class TPersistedGeneration
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}