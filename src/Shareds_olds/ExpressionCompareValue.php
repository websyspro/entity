<?php

namespace Websyspro\Entity\Shareds;

class ExpressionCompareValue
{
  public function __construct(
    array $scopes,
    public array $tokens    
  ){
    $this->startups( $scopes, $tokens );
  }

  private function startups(
    array $scopes,
    array $tokens
  ): void {}
}