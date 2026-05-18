<?php

namespace Websyspro\Entity\Shareds;

class ExpressionNegative
{
  public ExpressionNodes $expressionNodes;

  public function __construct(
    array $scopes,
    array $tokens    
  ){
    $this->startups( $scopes, $tokens );
  }

  private function startups(
    array $scopes,
    array $tokens
  ): void {
    $this->expressionNodes = new ExpressionNodes(
      $scopes, ExpressionUtil::slice( $tokens, 1 )
    );    
  }  
}