<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Entity\Enums\ColumnType;

class CompareField
{
  public Entity $entity;
  public Field $field;
  public ColumnType $columnType;

  public function __construct(
    public ExpressionCompare $expressionCompare,
    public Collection $tokens
  ){
    $this->startups();
    $this->startupsClear();
  }

  private function startups(
  ): void {
    [ $this->entity, $this->field, $this->columnType ] = CompareUtil::analyzed(
      $this->expressionCompare, $this->tokens
    );
  }

  private function startupsClear(
  ): void {
    unset( $this->expressionCompare, $this->tokens );
  }  
}