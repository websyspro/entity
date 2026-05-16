<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use Websyspro\Commons\Collection;
use Websyspro\Entity\Enums\UnaryNot;

class ExpressionUnary
{
  public Entity $entity;
  public Field $field;
  public UnaryNot $unaryNot;

  public function __construct(
    public Collection $tokens,
    public Collection $scopes,
    public Closure $closure
  ){
    $this->startups();
    $this->startupsIsNot();
  }

  private function startups(
  ): void {
    [ $this->entity, $this->field ] = CompareUtil::analyzed(
      $this->scopes, $this->tokens
    );
  }

  private function startupsIsNot(
  ): void {
    $this->unaryNot = ExpressionUtil::isUnaryNot( $this->tokens );
  } 
}