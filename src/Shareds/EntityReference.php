<?php

namespace Websyspro\Entity\Shareds;

class EntityReference
{
  public string $table;
  public string $key;
  public Entity $entity;

  public function __construct(
    public EntityStructure $entityStructure
  ){
    $this->definePrimaryKey();
    $this->defineClears();
  }

  private function definePrimaryKey(
  ): void {
    $this->entity = $this->entityStructure->entity;
    $this->table = $this->entityStructure->entity->table;
    
    if( $this->entityStructure->primaryKey->exist() ){
      $this->key = $this->entityStructure->primaryKey->first()->name;
    }
  }

  private function defineClears(
  ): void {
    unset(
      $this->entityStructure
    );
  }  
}