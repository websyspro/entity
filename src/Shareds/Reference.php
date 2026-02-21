<?php

namespace Websyspro\Entity\Shareds;

class Reference
{
  public string $key;
  public Entity $entity;

  public function __construct(
    EntityStructure $reference
  ){
    $this->entity = new Entity(
      $reference->entity->class
    );

    if( $reference->primaryKey->exist() === true ){
      $this->key = $reference->primaryKey->first()->name;
    }
  }
}