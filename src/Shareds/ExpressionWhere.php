<?php

namespace Websyspro\Entity\Shareds;

use Closure;

class ExpressionWhere
{
  public ExpressionNodes $expressionNodes;

  public function __construct(
    Closure $closure
  ){
    $this->startupsAnalyzed( $closure );
  }

  private function startupsAnalyzed(
    Closure $closure
  ): void {
    $this->expressionNodes = new ExpressionNodes(
      ExpressionUtil::getScopeFromTokens( ClosureUtil::getTokensFromClosure( $closure ), $closure ),
      ExpressionUtil::getContentsFromTokens( ClosureUtil::getTokensFromClosure( $closure ))
    );
  }  
}