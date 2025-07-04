<?php

namespace Websyspro\Entity\Core\Commons;

class Now
{
  public static function get(
  ): string {
    return date( "d/m/Y H:i:s" );
  }
}