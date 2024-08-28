<?php

namespace Websyspro\EntityApi\Commons;
use Websyspro\CommonApi\Utils;

class EntityUtils
{
  static function IsMigrate(): bool {
    [ "argv" => $argv ] = $_SERVER;
    
    $IsMigrate = Utils::Filter( $argv, fn( string $arg ) => (
      preg_match("/^\-\-/", $arg)
    ));

    return Utils::IsValidArray(
      $IsMigrate
    );
  }
}