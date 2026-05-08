<?php

namespace Websyspro\Entity\Shareds;

use Reflection;
use ReflectionFunction;
use Websyspro\Commons\Util;

class IncludeItem
{
  public Relationship $relationship;
  public WhereList $whereList;

  public function __construct(
    AbstractRepository $abstractRepository,
    ReflectionFunction $reflectionFunction,
    string $script
  ){
    $this->startup( $abstractRepository, $reflectionFunction, $script );
  }

  public function startup(
    AbstractRepository $abstractRepository,
    ReflectionFunction $reflectionFunction,
    string $script
  ): void {
    Util::match( "#->where\\(#", $script )
      ? [ $relationship, $whereList ] = explode( "->where(", $script )
      : [ $relationship ] = explode( "->where(", $script );

      
    if( isset( $relationship )){
      $this->relationship = new Relationship(
        $abstractRepository, $relationship
      );
    }
      
    if( isset( $whereList )){
      $this->whereList = new WhereList( 
        $abstractRepository, $reflectionFunction, $whereList
      );
    }
  }
}