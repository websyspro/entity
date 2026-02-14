<?php

namespace Websyspro\Entity\Core\Commons;

use Websyspro\Commons\Util;

class GuidV4
{
  public static function get(
  ): string {
    return Util::guidV4();
  }
}