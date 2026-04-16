<?php

namespace Websyspro\Entity\Interfaces;

class Entity
{
  public function __construct(
    public string $class,
    public string|null $alias = null,
    public string|null $table = null,
  ){}
}