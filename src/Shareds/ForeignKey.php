<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Util;

class ForeignKey
{
  public string $key;
  public Entity $entity;
  public EntityReference $entityReference;

  public function __construct(
    public Column $column,
    public EntityStructure $entityStructure
  ){
    $this->defineReference();
    $this->defineClears();
  }

  private function defineReference(
  ): void {
    $entityStructureReference = Util::callUserClassFN( 
      $this->column->instance->entityReference, "getAttributes", []
    );

    if( $entityStructureReference instanceof EntityStructure ){
      if( Util::sizeArray( $entityStructureReference->primaryKey ) !== 0 ){
        $this->key = $this->column->name;
        $this->entity = $this->entityStructure->entity;
        $this->entityReference = new EntityReference( 
          $entityStructureReference
        );
      }
    }
  }

  private function defineClears(
  ): void {
    unset( 
      $this->column,
      $this->entityStructure
    );
  }   
}