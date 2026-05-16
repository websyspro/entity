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
  }

  public function get(
  ): string {
    return $this->unaryNot === UnaryNot::Yes 
      ? Util::sprintFormat( "%s %s (Select 1 from %s Where %s)", [ $this->defineUnaryNot(), $this->defineSubQueryEvent(), $this->defineEntityFromScope(), $this->defineBuildScript() ]) 
      : Util::sprintFormat( "%s (Select 1 from %s Where %s)", [ $this->defineSubQueryEvent(), $this->defineEntityFromScope(), $this->defineBuildScript() ]);
  }  

  public function defineUnaryNot(
  ): string {
    return match( $this->unaryNot ){
      UnaryNot::Yes => "Not",
        default => ""
    };
  }
  
  public function defineSubQueryEvent(
  ): string {
    return match( $this->subQueryEvent ){
      SubQueryEvent::Any => "Exists",
        default => ""
    };
  }

  private function defineEntityFromScope(
  ): string|null {
    [ $scope ] = $this->expressionNode->scopes
      ->slice( -1, 1)->toArray();
      
    if( $scope instanceof Scope ){
      return $scope->entity->alias;
    }
    
    return null; 
  }

  public function defineBuildScript(
  ): string {
    return ExpressionUtil::expressionBuildScript( $this->expressionNode );
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
}