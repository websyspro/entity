<?php

namespace Websyspro\Entity\Shareds;

class PersistedRequireds
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}