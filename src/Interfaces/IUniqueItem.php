<?php

namespace Websyspro\Entity\Interfaces;

class IUniqueItem
{
  public function __construct(
    public string $name,
    public int $uniqueGroup
  ){}
}