<?php

namespace Websyspro\Entity\Interfaces;

class IUniqueNameItems
{
  public function __construct(
    public string $name,
    public string $columns
  ){}
}