<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use Websyspro\Commons\Collection;

class ExpressionCompare
{
  public function __construct(
    public Collection $tokens,
    public Collection $scopes,
    public Closure $closure
  ){}
}