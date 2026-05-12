<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use Websyspro\Commons\Collection;

class ExpressionSubQuery
{
  public ExpressionNode $expressionNode;

  public function __construct(
    Collection $tokens,
    Collection $scopes,
    Closure $closure
  ){
    $this->startups(
      $tokens, $scopes, $closure
    );
  }
  
  private function startups(
    Collection $tokens,
    Collection $scopes,
    Closure $closure
  ): void {
    $this->expressionNode = new ExpressionNode(
      $tokens->spliceOut( ExpressionUtil::find( $tokens, T_FN )), $scopes, $closure
    );
  }
}