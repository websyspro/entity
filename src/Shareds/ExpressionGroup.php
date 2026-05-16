<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use Closure;

class ExpressionGroup
{
  public ExpressionNode $expressionNode;

  public function __construct(
    Collection $tokens,
    Collection $scopes,
    Closure $closure
  ){
    $this->startups( $tokens, $scopes,$closure );
  }

  public function get(
  ): string {
    return Util::sprintFormat( "(%s)", [
      ExpressionUtil::expressionBuildScript(
        $this->expressionNode
      )
    ]);
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