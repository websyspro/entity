<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Util;

class ExpressionCompareBetween
{
  public function __construct(
    public CompareField|CompareValue|CompareUnary $sideLeft,
    public CompareField|CompareValue $valueStart,
    public CompareField|CompareValue $valueEnd
  ){}

  public function get(
  ): string {
    return Util::sprintFormat( "%s.%s Between %s And %s", [
      $this->sideLeft->entity->alias, $this->sideLeft->field->alias,
      $this->valueStart->value, $this->valueEnd->value,
    ]);
  }  
}