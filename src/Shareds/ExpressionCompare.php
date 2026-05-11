<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

class ExpressionCompare
{
  public function __construct(
    public Collection $tokens
  ){}
}