<?php

namespace Websyspro\Entity\Shareds;

use ReflectionFunction;

class WhereList
{
  public UseItem $useItem;
  public WhereBody $whereBody;
  
  public function __construct(
    AbstractRepository $abstractRepository,
    ReflectionFunction $reflectionFunction,
    string $where,
  ){
    $this->startup( 
      $abstractRepository, 
      $reflectionFunction,
      $where
    );
  }

  public function startup(
    AbstractRepository $abstractRepository,
    ReflectionFunction $reflectionFunction,
    string $where,
  ): void {
    [ $whereParameter, $whereBody ] = explode(
      "=>", $where, 2
    );

    [ $entity, $parameter ] = $abstractRepository
      ->entityWithParam( $whereParameter );
    
    if( isset( $entity )){
      $useItem = $abstractRepository->useList->getByName( $entity );
      $statics = $abstractRepository->staticsFromFunction( $reflectionFunction );

      if( $useItem instanceof UseItem ){
        $this->useItem = $useItem->setParameter( $parameter );
        $this->whereBody = new WhereBody( 
          $abstractRepository, $reflectionFunction, $useItem, $statics, $whereBody
        );
      }
    }
  }
}