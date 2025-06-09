<?php

namespace Websyspro\Entity\Core\Commons;

class Now
{
  public static function Get(
  ): string {
    return date( "d/m/Y H:i:s" );
  }
}