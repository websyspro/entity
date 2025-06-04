<?php

namespace Websyspro\Entity\Interfaces;

class IPersistedColumn
{
  public function __construct(
    public string $table,
    public string $name,
    public string $type
  ){}
}