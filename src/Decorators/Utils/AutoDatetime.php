<?php

namespace Websyspro\Entity\Decorators\Utils;

use Websyspro\Connection\Database;
use Websyspro\Connection\Enums\DriverType;
use DateTime;

class AutoDatetime
{
  public static function generate(): string
  {
    return match( Database::driver()){
      DriverType::SqlServer
        => static::dateTime()->format( "Y-m-d H:i:s.v" ),
      DriverType::PostgreSQL 
        => static::dateTime()->format( "Y-m-d H:i:s.v" ),
      default 
        => static::dateTime()->format( "Y-m-d H:i:s" ),
    };
  }

  private static function dateTime(
  ): DateTime {
    return new DateTime();
  }
}
