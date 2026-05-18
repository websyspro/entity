<?php

namespace Websyspro\Entity\Shareds;

class ExpressionGroup
{
  public ExpressionNodes $expressionNodes;

  public function __construct(
    array $scopes,
    array $tokens    
  ){
    $this->startupsAnalyzed(
      $scopes, $tokens
    );
  }

  private function startupsAnalyzed(
    array $scopes,
    array $tokens    
  ): void {
    $this->expressionNodes = new ExpressionNodes(
      $scopes, ExpressionUtil::slice( $tokens, 1, -1 )
    );
  }  
}