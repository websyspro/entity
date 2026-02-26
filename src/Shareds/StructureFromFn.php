<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\EntityRoot;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionFunction;

class StructureFromFn
{
  public Collection $parameters;
  public Collection $statics;
  public Collection $joins;

  public function __construct(
    public ReflectionFunction $reflectionFunction
  ){
    $this->setParametersList();
    $this->setStaticsList();
    $this->setJoinsList();
    $this->setUsesList();
  }
  
  private function setParametersList(
  ): void {
    foreach( $this->reflectionFunction->getParameters() as $parameter ){
      if( $parameter instanceof ReflectionParameter ){
        if( isset( $this->parameters ) === false ){
          $this->parameters = new Collection();
        }

        $this->parameters->add(
          new Parameter( 
            $parameter->getName(), 
            $parameter->getType() instanceof ReflectionNamedType
              ? Util::callUserClassFN( 
                $parameter->getType()->getName(), 
                "getAttributes", 
                []
              ) : null
          ), $parameter->getType()
        );
      }
    }
  }

  private function setStaticsList(
  ): void {
    $this->statics = new Collection(
      $this->reflectionFunction->getStaticVariables()
    );
  }

  private function setJoinsListByItem(
    Entity|null $entity = null,
    Entity|null $entityReference = null,
    array $entityHistory = [],
    AttributeType $attributeType = AttributeType::oneToOne
  ): void {
    $joinAlreadyAdded = $this->joins->where(
      fn( HierarchyJoin $hierarchyJoin ) => (
        $hierarchyJoin->entity->class === $entity->class
      )
    );

    if( $joinAlreadyAdded->exist() === false ){
      $parameter = $entity !== null 
        ? $this->parameters->get( $entity->class ) 
        : $this->parameters->first();

      if( $parameter !== null ){
        $entityHistory = array_merge( 
          $entityHistory, [ 
            $attributeType 
          ]
        );  

        $entityRoot = Util::sizeArray(
          Util::where( $entityHistory, 
          fn( AttributeType $attributeType ) => $attributeType === AttributeType::oneToMany )
        ) === 0 ? EntityRoot::Yes : EntityRoot::No;

        if( $entity !== null ){
          $entityFromParameter = $this->parameters->get( $parameter->entityStructure->entity->class );
          $entityParentFromParameter = $this->parameters->get( $entityReference->class );

          if( $attributeType === AttributeType::oneToOne ){
            if( $entityParentFromParameter instanceof Parameter ){
              $foreigns = $entityParentFromParameter->entityStructure->foreigns->where( fn( ForeignKey $foreignKey ) =>  (
                $foreignKey->entityReference->entity->class === $parameter->entityStructure->entity->class && 
                $foreignKey->entity->class === $entityReference->class
              ));
            }
          } else
          if( $attributeType === AttributeType::oneToMany ) {
            if( $entityFromParameter instanceof Parameter ){
              $foreigns = $entityFromParameter->entityStructure->foreigns->where( fn(ForeignKey $foreignKey ) =>  (
                $foreignKey->entityReference->entity->class === $entityReference->class && 
                $foreignKey->entity->class === $parameter->entityStructure->entity->class
              ));
            }
          }
        }

        $this->joins->add(
          new HierarchyJoin(
            $parameter->entityStructure->entity,
            $entityReference,
            $entityHistory,
            $entityRoot,
            isset( $foreigns ) ? $foreigns->first() : null
          ), $parameter->entityStructure->entity->class
        );

        $parameter->entityStructure->oneToOne->mapper(
          fn(Entity $entity) => $this->setJoinsListByItem( 
            $entity, 
            $parameter->entityStructure->entity, 
            $entityHistory, 
            AttributeType::oneToOne
          )
        );    

        $parameter->entityStructure->oneToMany->mapper(
          fn(Entity $entity) => $this->setJoinsListByItem( 
            $entity, 
            $parameter->entityStructure->entity, 
            $entityHistory,
            AttributeType::oneToMany
          )
        );        
      }
    }
  }

  private function setJoinsList(
  ): void {
    if( isset( $this->joins ) === false ){
      $this->joins = new Collection();
    }

    $this->setJoinsListByItem();
  }

  private function setUsesList(
  ): void {

  }
}