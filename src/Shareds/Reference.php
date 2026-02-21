<?php

namespace Websyspro\Entity\Shareds;

class Reference
{
  public string $table;
  public string $key;
  public string $entity;

  public function __construct(
    EntityStructure $reference
  ){
    $this->table = $reference->entity->table;
    $this->entity = $reference->entity->class;
    if( $reference->primaryKey->exist() === true ){
      $this->key = $reference->primaryKey->first()->name;
    }
  }
}