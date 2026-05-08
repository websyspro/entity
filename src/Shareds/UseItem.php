<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;

class UseItem
{
  public string $path;
  public string $entity;
  public string $alias;
  public string $parameter;

  public function __construct(
    string|null $use = null
  ){
    if($use !== null ){
      $this->startup( $use );
    }
  }

  public function getPath(
  ): string {
    return implode( DIRECTORY_SEPARATOR, [
      $this->path, $this->entity
    ]);
  }
  
  public function setParameter(
    string $parameter
  ): UseItem {
    $this->parameter = $parameter;
    return $this;
  }

  private function startup(
    string|null $use = null
  ): void {
    $uses = new Collection(
      explode( "\\", $use )
    );

    [ $this->path, $this->entity ] = array_merge([
      $uses->slice( 0, -1 )->join( DIRECTORY_SEPARATOR ),
    ], $uses->slice( -1 )->toArray());

    if( Util::match( "#\sas\s#", $this->entity )){
      [ $this->entity, $this->alias ] = explode( " as ", $this->entity );
    }
  }
}