<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;

class IncludeList
extends Collection
{
  public function mapper(
    callable|object $fn
  ): IncludeList {
    if( is_callable( $fn ) === false ){
      return new Collection(
        Util::mapper(
          $this->items, fn(mixed $item) => (
              Util::hydrate( $item, $fn )
            ) 
          )
      );
    }

    return new IncludeList(
      Util::mapper(
        $this->items, $fn
      )
    );
  }
}