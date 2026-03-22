<?php

namespace Websyspro\Entity\Shareds;

class TokenEntity
{
  public string $table;
  public string $field;
  public string $class;

  public function __construct(
    Entity $entity,
    string $field
  ){
    $this->field = $field;
    $this->table = $entity->table;
    $this->class = $entity->class;
  }
}