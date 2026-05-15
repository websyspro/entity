<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\SubQueryEvent;
use Websyspro\Entity\Enums\UnaryNot;
use Websyspro\Commons\Collection;
use Closure;

class ExpressionSubQuery
{
  public ExpressionNode $expressionNode;
  public SubQueryEvent $subQueryEvent;
  public UnaryNot $unaryNot;

  public function __construct(
    public Collection $tokens,
    public Collection $scopes,
    public Closure $closure
  ){
    $this->startups();
    $this->startupsAnalyzedIsNot();
    $this->startupsAnalyzedSubQueryEvent();
    $this->startupsAnalyzedClear();
  }
  
  private function startups(
  ): void {
    $this->expressionNode = new ExpressionNode(
      ExpressionUtil::dropUnnecessaryEndScripts(
        $this->tokens->spliceOut( ExpressionUtil::find( $this->tokens, T_FN ))
      ), $this->scopes, $this->closure
    );
  }

  private function startupsAnalyzedIsNot(
  ): void {
    $this->unaryNot = ExpressionUtil::isUnaryNot( $this->tokens );
  }

  private function startupsAnalyzedSubQueryEvent(
  ): void {
    $this->subQueryEvent = ExpressionUtil::isSubQuerEvent( $this->tokens );
  }  

  private function startupsAnalyzedClear(
  ): void {
    unset( $this->tokens, $this->scopes, $this->closure );
  }
}