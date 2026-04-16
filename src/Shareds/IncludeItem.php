<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Shareds\UseList;
use Websyspro\Commons\Util;

class IncludeItem
{
  public Relationship $relationship;
  public string $where;

  public function __construct(
    AbstractRepository $abstractRepository,
    string $script
  ){
    $this->startup( $abstractRepository, $script );
  }

  public function startup(
    AbstractRepository $abstractRepository,
    string $script
  ): void {
    Util::match( "#->where\\(#", $script )
      ? [ $relationship, $this->where ] = explode( "->where(", $script )
      : [ $relationship ] = explode( "->where(", $script );

    if( isset( $relationship )){
      $this->relationship = new Relationship(
        $abstractRepository, $relationship
      );
    }
  }
}