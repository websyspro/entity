<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Entity\Enums\MultiLine;
use Websyspro\Entity\Shareds\Entity;

class Join
{
  public Entity $entity;
  public MultiLine $multiLine;
  public string $tableBase;
  public string $table;
  public string $key;
  public string $referenceTable;
  public string $referenceKey;

  public function __construct(
    MultiLine $multiLine,
    Parameter $parameterChild,
    Parameter $parameterParent
  ){
    $this->defineJoin(
      $multiLine, 
      $parameterChild, 
      $parameterParent
    );
  }

  private function defineJoin(
    MultiLine $multiLine,
    Parameter $parameterChild,
    Parameter $parameterParent    
  ): void {
    $this->multiLine = $multiLine;
    $this->entity = $parameterChild->entityStructure->entity;
    $this->tableBase = $parameterChild->entityStructure->entity->table;

    if( $this->multiLine === MultiLine::No ){
      $existForeignInParent = isset( 
        $parameterParent->entityStructure->foreigns[
          $parameterChild->entityStructure->entity->class
        ]
      );

      if( $existForeignInParent ){
        $this->table = $parameterParent->entityStructure->entity->table;
        $this->key = $parameterParent->entityStructure->foreigns[
          $parameterChild->entityStructure->entity->class 
        ]->name;
      }

      $this->referenceTable = $parameterChild->entityStructure->entity->table; 
      $this->referenceKey = reset( $parameterChild->entityStructure->primaryKey );

    } else {
      $existForeignInChild = isset( 
        $parameterChild->entityStructure->foreigns[
          $parameterParent->entityStructure->entity->class
        ]
      );

      if( $existForeignInChild ){
        $this->referenceTable = $parameterChild->entityStructure->entity->table;
        $this->referenceKey = $parameterChild->entityStructure->foreigns[
          $parameterParent->entityStructure->entity->class 
        ]->name;        
      }

      $this->table = $parameterParent->entityStructure->entity->table; 
      $this->key = reset( $parameterParent->entityStructure->primaryKey );      
    }
  }
}