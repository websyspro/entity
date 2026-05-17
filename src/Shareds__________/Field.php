<?php

namespace Websyspro\Entity\Shareds;

class Field
{
  public function __construct(
    public string $name,
    public string $alias
  ){}
}