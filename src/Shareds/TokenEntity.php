<?php

namespace Websyspro\Entity\Shareds;

class TokenEntity
{
  public string $table;
  public string $class;

  public function __construct(
    Entity $entity,
    public string $field
  ){
    $this->table = $entity->table;
    $this->class = $entity->class;
  }
}