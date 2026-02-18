<?php

namespace Websyspro\Entity\Shareds;

class Reference
{
  public string $table;
  public string $key;

  public function __construct(
    EntityStructure $reference
  ){
    $this->table = $reference->entity->table;
    if( $reference->primaryKey->exist() === true ){
      $this->key = $reference->primaryKey->first()->name;
    }
  }
}