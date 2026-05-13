<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

class CompareUnary
{
  public Entity $entity;
  public Field $field;
  public bool $isUnaryNot;

  public function __construct(
    public ExpressionCompare $expressionCompare,
    public Collection $tokens
  ){
    $this->startups();
    $this->startupsIsNot();
    $this->startupsClear();
  }

  private function startups(
  ): void {
    [ $this->entity, $this->field ] = CompareUtil::analyzed(
      $this->expressionCompare, $this->tokens
    );    
  }

  private function startupsIsNot(
  ): void {
    [ $tokenIsUnaryNot ] = $this->tokens->toArray();
    if( $tokenIsUnaryNot instanceof Token ){
      $this->isUnaryNot = $tokenIsUnaryNot->id !== T_VARIABLE && $tokenIsUnaryNot->value === "!";
    }
  }

  private function startupsClear(
  ): void {
    unset( $this->expressionCompare, $this->tokens );
  }  
}