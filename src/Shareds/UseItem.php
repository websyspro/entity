<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;

class UseItem
{
  public string $path;
  public string $entity;
  public string $alias;

  public function __construct(
    string $use
  ){
    $this->startup( $use );
  }

  public function getPath(
  ): string {
    return implode( DIRECTORY_SEPARATOR, [
      $this->path, $this->entity
    ]);
  }

  private function startup(
    string $use
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