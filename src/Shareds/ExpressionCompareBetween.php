<?php

namespace Websyspro\Entity\Shareds;

use Closure;

class ExpressionCompareBetween
{
  public function __construct(
    public CompareField|CompareValue|CompareUnary $sideLeft,
    public CompareField|CompareValue $valueStart,
    public CompareField|CompareValue $valueEnd,
    public Closure $closure
  ){
    $this->startupsParsers();
    $this->startupsClear();
  }

  private function startupsParsers(
  ): void {
    $this->valueStart->value = $this->sideLeft->columnType
      ->Encode( ClosureUtil::createParam( $this->closure, $this->valueEnd->value ));
    $this->valueEnd->value = $this->sideLeft->columnType
      ->Encode( ClosureUtil::createParam( $this->closure, $this->valueEnd->value ));
  } 
  
  private function startupsClear(
  ): void {
    unset( $this->closure );
  }
}