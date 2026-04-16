<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Shareds\UseList;
use Websyspro\Commons\Util;

class Relationship
{
  public UseItem $useItem;
  public TargetItem $targetItem;
  public string $parameter;

  public function __construct(
    AbstractRepository $abstractRepository,
    string $relationship
  ){
    $this->startup( 
      $abstractRepository, 
      $relationship
    );
  }

  public function startup(
    AbstractRepository $abstractRepository,
    string $relationship    
  ): void {
    [ $parameter, $target ] = explode(
      "=>", $relationship
    );

    [ $entity, $parameter ] = explode( " ", Util::replace([ 
      "#^(\s*)?fn\\(\s*#", "#\s*\\)\s*$#" ], $parameter
    ));

    if( isset( $entity )){
      $useItem = $abstractRepository->useList->getByName( $entity );

      if( $useItem instanceof UseItem ){
        $this->useItem = $useItem;
        $this->parameter = $parameter;
        $this->targetItem = new TargetItem( 
          $abstractRepository, $useItem, $target
        );
      }
    }
  }
}