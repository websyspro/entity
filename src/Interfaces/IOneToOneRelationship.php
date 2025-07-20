<?php

namespace Websyspro\Entity\Interfaces;

class IOneToOneRelationship
{
  public function __construct(
    public string $table,
    public string $referece
  ){}
}