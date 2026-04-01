<?php

namespace Websyspro\Entity\Shareds;

class TokenEntity
{
  public function __construct(
    public Entity $entity,
    public string $field
  ){
    // $this->field = $field;
    // $this->table = $entity->table;
    // $this->class = $entity->class;
  }
}