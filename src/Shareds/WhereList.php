<?php

namespace Websyspro\Entity\Shareds;

use ReflectionFunction;

class WhereList
{
  public UseItem $useItem;
  public WhereBody $whereBody;
  
  public function __construct(
    public AbstractRepository $abstractRepository,
    public ReflectionFunction $reflectionFunction,
    string $where,
    bool $delph = false
  ){
    $this->startup( 
      $where,
      $delph
    );
  }

  public function startup(
    string $where,
    bool $delph
  ): void {
    [ $whereParameter, $whereBody ] = explode(
      "=>", $where, 2
    );

    [ $entity, $parameter ] = $this->abstractRepository
      ->entityWithParam( $whereParameter );
    
    if( isset( $entity )){
      $useItem = $this->abstractRepository->useList->getByName( $entity );
      $statics = $this->abstractRepository->staticsFromFunction( $this->reflectionFunction );

      if( $useItem instanceof UseItem ){
        $this->useItem = $useItem->setParameter( $parameter );
        $this->whereBody = new WhereBody( 
          $this->abstractRepository, $this->reflectionFunction, $useItem, $statics, $whereBody, $delph
        );
      }
    }
  }

  public function entityAlias(
  ): string {
    return $this->abstractRepository
      ->entityStructure( $this->useItem->getPath() )->entity->alias;
  }
}