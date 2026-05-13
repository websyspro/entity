<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

class CompareValue
{
  public function __construct(
    public ExpressionCompare $expressionCompare,
    public Collection $tokens  
  ){
    $this->startups();
    $this->startupsAnalyzed();
    $this->startupsAnalyzedClear();
  }

  private function startups(
  ): void {

  }

  private function startupsAnalyzed(
  ): void {}
  
  private function startupsAnalyzedClear(
  ): void {
    unset( $this->expressionCompare );
  }  
}