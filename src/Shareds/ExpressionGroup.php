<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use Websyspro\Commons\Collection;

class ExpressionGroup
{
  public ExpressionNode $expressionNode;

  public function __construct(
    public Collection $tokens,
    public Collection $scopes,
    public Closure $closure
  ){
    $this->startups();
    $this->startupsClear();
  }

  private function startups(
  ): void {
    $this->expressionNode = new ExpressionNode(
      ExpressionUtil::extractGroup( $this->tokens ), $this->scopes, $this->closure     
    );
  }

  private function startupsClear(
  ): void {
    unset( $this->tokens, $this->scopes, $this->closure );
  }
}