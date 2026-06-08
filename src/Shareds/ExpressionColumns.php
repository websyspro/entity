<?php

namespace Websyspro\Entity\Shareds;

use Closure;

class ExpressionColumns
{
  public function __construct(
    public Closure $closure
  ){}

  public function get(
  ): array {
    return [];
  }
}