<?php

namespace Websyspro\Entity\Shareds_;

class Param
{
  public function __construct(
    public string|array $value,
    public string $key
  ){}
}