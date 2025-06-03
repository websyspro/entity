<?php

namespace Websyspro\Entity\Shareds;

class TPersistedRequireds
{
  public function __construct(
    public string $table,
    public string $name
  ){}
}