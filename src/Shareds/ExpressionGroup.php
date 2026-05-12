<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use Websyspro\Commons\Collection;

class ExpressionGroup
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
      ExpressionUtil::extractGroup( $tokens ), $scopes, $closure     
    );
  }
}