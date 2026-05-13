<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

class CompareField
{
  public Entity $entity;
  public Field $field;

  public function __construct(
    public ExpressionCompare $expressionCompare,
    public Collection $tokens
  ){
    $this->startups();
    $this->startupsClear();
  }

  private function startups(
  ): void {
    [ $this->entity, $this->field ] = CompareUtil::analyzed(
      $this->expressionCompare, $this->tokens
    );
  }

  private function startupsClear(
  ): void {
    unset( $this->expressionCompare, $this->tokens );
  }  
}