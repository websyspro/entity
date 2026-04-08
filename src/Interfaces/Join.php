<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Entity\Enums\MultiLine;

class Join
{
  public Entity $entity;
  public Entity $entityParent;
  public MultiLine $multiLine;
  public MultiLine $multiLineReal;
  public string $tableBase;
  public string $table;
  public string $key;
  public string $referenceTable;
  public string $referenceKey;

  public function __construct(
    MultiLine $multiLine,
    MultiLine $multiLineReal,
    Parameter $parameterChild,
    Parameter $parameterParent
  ){
    $this->defineJoin(
      $multiLine,
      $multiLineReal,
      $parameterChild, 
      $parameterParent
    );
  }

  private function isForeignsExist(
    Parameter $parameterA,
    Parameter $parameterB
  ): bool {
    return isset(
      $parameterA->entityStructure->foreigns[
        $parameterB->entityStructure->entity->class
      ]
    );
  }

  private function getForeigns(
    Parameter $parameterA,
    Parameter $parameterB
  ): ItemForeignKey {
    return $parameterA->entityStructure->foreigns[
      $parameterB->entityStructure->entity->class
    ];
  } 
  
  private function setJoinRelationship(
    Parameter $parameterA,
    Parameter $parameterB
  ): array {
    return [
      $parameterA->entityStructure->foreigns[ $parameterB->entityStructure->entity->class ]->table,
      $parameterA->entityStructure->foreigns[ $parameterB->entityStructure->entity->class ]->key,
      $parameterA->entityStructure->foreigns[ $parameterB->entityStructure->entity->class ]->referenceTable,
      $parameterA->entityStructure->foreigns[ $parameterB->entityStructure->entity->class ]->referenceKey
    ];
  }

  private function defineJoin(
    MultiLine $multiLine,
    MultiLine $multiLineReal,
    Parameter $parameterChild,
    Parameter $parameterParent    
  ): void {
    $this->multiLine = $multiLine;
    $this->multiLineReal = $multiLineReal;
    $this->entity = $parameterChild->entityStructure->entity;
    $this->entityParent = $parameterParent->entityStructure->entity;
    $this->tableBase = $parameterChild->entityStructure->entity->table;

    $foreignChildToParent = $this->isForeignsExist( $parameterChild, $parameterParent );
    $foreignParentToChild = $this->isForeignsExist( $parameterParent, $parameterChild );

    /** Check exist foreigns de um lado ou outro */
    if( $foreignChildToParent || $foreignParentToChild ){
      if( $foreignChildToParent ){
        [ $this->table, $this->key, $this->referenceTable, $this->referenceKey
        ] = $this->setJoinRelationship( $parameterChild, $parameterParent );
      } else 
      if( $foreignParentToChild ){
        [ $this->referenceTable, $this->referenceKey, $this->table, $this->key 
        ] = $this->setJoinRelationship( $parameterParent, $parameterChild );
      }
    }
  }
}