<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Util;

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
    
    if( Util::sizeArray( $this->entityStructure->primaryKey ) !== 0 ){
      [ $primaryKey ] = array_values( $this->entityStructure->primaryKey );
      if( $primaryKey instanceof PrimaryKey ){
        $this->key = $primaryKey->name;
      }
    }
  }

  private function defineClears(
  ): void {
    unset(
      $this->entityStructure
    );
  }  
}