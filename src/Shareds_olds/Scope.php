<?php

namespace Websyspro\Entity\Shareds;

use Closure;

class Scope
{
  public function __construct(
    public string $variable,
    public string $variableType,
    public Closure $closure,
  ){}
}