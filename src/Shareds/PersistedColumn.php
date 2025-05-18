<?php

namespace Websyspro\Entity\Shareds;

class PersistedColumn
{
  public function __construct(
    public string $table,
    public string $name,
    public string $type
  ){}
}