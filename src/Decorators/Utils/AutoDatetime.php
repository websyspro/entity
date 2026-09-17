<?php

namespace Websyspro\Entity\Decorators\Utils;

class AutoDatetime
{
  public static function generate(): string
  {
    return date('Y-m-d H:i:s');
  }
}
