<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

class UseList 
extends Collection
{
  public function getByName(
    string $name 
  ): UseItem|null {
    [ $use ] = $this->where(
      fn( UseItem $use ) => $use->entity === $name
    )->toArray();

    if( $use instanceof UseItem ){
      return $use;
    }
 
    return null;
  }
}