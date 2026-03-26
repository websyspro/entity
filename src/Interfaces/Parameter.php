<?php

namespace Websyspro\Entity\Interfaces;

class Parameter
{
  public function __construct(
    public string $name,
    public string $entity
  ){}
}