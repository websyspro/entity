<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use Websyspro\Commons\Collection;
use Websyspro\Entity\Enums\UnaryNot;

class CompareUnary
{
  public Entity $entity;
  public Field $field;
  public UnaryNot $unaryNot;

  public function __construct(
    public Collection $closure,
    public Collection $tokens
  ){
    $this->startups();
    $this->startupsIsNot();
    $this->startupsClear();
  }

  private function startups(
  ): void {
    [ $this->entity, $this->field ] = CompareUtil::analyzed(
      $this->closure, $this->tokens
    );    
  }

  private function startupsIsNot(
  ): void {
    $this->unaryNot = ExpressionUtil::isUnaryNot( $this->tokens );
  }

  private function startupsClear(
  ): void {
    unset( $this->expressionCompare, $this->tokens );
  }  
}