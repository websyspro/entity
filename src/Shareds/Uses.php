<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;

class Uses
{
  public function __construct(
    public Collection $list
  ){
    $this->startups();
  }

  public function getUse(
    string $alias
  ): UsesItem|null {
    $uses = $this->list->where( 
      fn(UsesItem $usesItem ) => (
        $usesItem->alias === $alias
      )
    );

    if( $uses->exist() === false){
      return null;
    }

    [ $usesItem ] = $uses->toArray();
    if( $usesItem instanceof UsesItem ){
      return $usesItem;
    }

    return null;
  }

  private function startups(
  ): void {
    $this->list = $this->list->mapper(
      fn( string $use ) => new UsesItem(
        Util::replace( [ "#^use\s*#", "#;$#" ], trim( $use ))
      )
    );
  }
}