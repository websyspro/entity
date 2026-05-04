<?php

namespace Websyspro\Entity\Shareds;

class WhereList
{
  public UseItem $useItem;
  public WhereBody $whereBody;
  public string $parameter;

  public function __construct(
    AbstractRepository $abstractRepository,
    string $where    
  ){
    $this->startup( $abstractRepository, $where );
  }

  public function startup(
    AbstractRepository $abstractRepository,
    string $where 
  ): void {
    [ $whereParameter, $whereBody ] = explode(
      "=>", $where
    );

    [ $entity, $parameter ] = $abstractRepository
      ->entityWithParam( $whereParameter );
    
    if( isset( $entity )){
      $useItem = $abstractRepository->useList->getByName( $entity );

      if( $useItem instanceof UseItem ){
        $this->useItem = $useItem;
        $this->parameter = $parameter;
        $this->whereBody = new WhereBody( 
          $abstractRepository, $useItem, $whereBody
        );
      }
    }
  }
}