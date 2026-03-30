<?php

namespace Websyspro\Entity\Shareds;

class ForeignKey
{
  public string $name;
  public Entity $entity;

  public function __construct(
    Column $column
  ){
    $this->defineInits( $column );
  }

  private function defineInits(
    Column $column
  ): void {
    $this->name = $column->name;
    $this->entity = new Entity(
      $column->instance->entityReference
    );
  }
}