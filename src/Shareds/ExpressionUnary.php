<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Util;
use Websyspro\Entity\Enums\UnaryNot;
use Websyspro\Commons\Collection;

class ExpressionUnary
{
  public Entity $entity;
  public Field $field;
  public UnaryNot $unaryNot;

  public function __construct(
    Collection $tokens,
    Collection $scopes
  ){
    $this->startups(
      $tokens, $scopes
    );
  }

  private function startups(
    Collection $tokens,
    Collection $scopes
  ): void {
    [ $this->entity, $this->field ] = CompareUtil::analyzed( $scopes, $tokens );
    $this->unaryNot = ExpressionUtil::isUnaryNot( $tokens );
  }

  public function get(
  ): string {
    return $this->unaryNot 
      ? Util::sprintFormat( "%s.%s = 0", [ $this->entity->alias, $this->field->alias ])
      : Util::sprintFormat( "%s.%s = 1", [ $this->entity->alias, $this->field->alias ]);
  }  
}