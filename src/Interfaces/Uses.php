<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use Websyspro\Entity\Consts\Patterns;

class Uses
{
  public string $namespace;
  public string $entity;
  public string $alias;

  public function __construct(
    string $use
  ){
    if( Util::match( Patterns::PATTERN_NAMESPACE_ALIAS, $use )){
      $matchAllArr = Util::matchAll( Patterns::PATTERN_NAMESPACE_ALIAS, $use );
      print_r( $matchAllArr );
      if( $matchAllArr !== null ){
        [ $this->namespace, $this->alias ] = $matchAllArr;
     }
    } else {
      $this->namespace = $use;
    }

    $namespacePaths = new Collection( explode( Patterns::PATTERN_NAMESPACE_BREAKS, $use ) );
    [ $this->namespace, $this->entity ] = [
      $namespacePaths->slice( 0, -1 )->join( Patterns::PATTERN_NAMESPACE_BREAKS ),
      $namespacePaths->slice( -1 )->join( Patterns::PATTERN_NAMESPACE_BREAKS )
    ];
  }
}