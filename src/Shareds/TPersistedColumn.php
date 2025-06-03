<?php

namespace Websyspro\Entity\Shareds;

class TPersistedColumn
{
  public function __construct(
    public string $table,
    public string $name,
    public string $type
  ){}
}