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
    Collection $scopes,
    Collection $tokens
  ){
    $this->startups(
      $scopes, $tokens
    );
  }

  private function startups(
    Collection $scopes,
    Collection $tokens    
  ): void {
    [ $this->entity, $this->field, $this->columnType
    ] = CompareUtil::analyzed( $scopes, $tokens );
  }
}