<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

class ExpressionGroup
{
  public function __construct(
    public Collection $tokens,
    public Collection $scopes
  ){
    $this->startups();
    $this->startupsAnalyzed();
  }

  private function startups(
  ): void {
    $this->tokens = ExpressionUtil::extractGroup( $this->tokens );
  }

  private function startupsAnalyzed(
  ): void {
    $this->tokens = ExpressionUtil::expressionStructureValid(
      $this->tokens, $this->scopes
    );
  }   
}