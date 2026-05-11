<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

class ExpressionSubQuery
{
  public ExpressionNode $expressionNode;

  public function __construct(
    Collection $tokens,
    Collection $scopes
  ){
    $this->startups(
      $tokens, $scopes
    );
  }
  
  private function startups(
    Collection $tokens,
    Collection $scopes
  ): void {
    $this->expressionNode = new ExpressionNode(
      $tokens->spliceOut( ExpressionUtil::find( $tokens, T_FN )), $scopes 
    );
  }
}