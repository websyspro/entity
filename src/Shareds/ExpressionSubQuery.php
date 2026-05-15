<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Util;
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

  public function get(
  ): string {
    return Util::sprintFormat( "%s %s (%s)", [
      $this->defineUnaryNot( $this->unaryNot ),
      $this->defineSubQueryEvent( $this->subQueryEvent ),
      $this->defineBuildScript( $this->expressionNode )
    ]);
  }  

  public static function defineUnaryNot(
    UnaryNot $unaryNot
  ): string {
    return match( $unaryNot ){
      UnaryNot::Yes => "Not",
        default => ""
    };
  }
  
  public static function defineSubQueryEvent(
    SubQueryEvent $subQuerEvent
  ): string {
    return match( $subQuerEvent ){
      SubQueryEvent::Any => "Exists",
        default => ""
    };
  }

  public function defineBuildScript(
    ExpressionNode $expressionNode
  ): string {
    return ExpressionUtil::expressionBuildScript( $expressionNode );
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