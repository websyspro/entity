<?php

namespace Websyspro\Entity\Shareds;

class ExpressionNodes
{
  public function __construct(
    public array $scopes,
    public array $tokens
  ){
    $this->startupsAnalyzed();
  }

  private function startupsAnalyzed(
  ): void {
    $this->tokens = ExpressionUtil::readTokensFromNodes( 
      $this->scopes, $this->tokens
    );

    unset($this->scopes);
  }  
}