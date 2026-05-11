<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

class ExpressionGroup
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
      ExpressionUtil::extractGroup( $tokens ), $scopes
    );
  }
}