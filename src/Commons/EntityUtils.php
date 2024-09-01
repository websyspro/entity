<?php

namespace Websyspro\Entity\Commons 
{
  use Websyspro\Common\Utils;
  
  class EntityUtils
  {
    static function IsMigrate(): bool {
      [ "argv" => $argv ] = $_SERVER;
      
      $IsMigrate = Utils::Filter($argv, fn(string $arg) => (
        preg_match("/^\-\-/", $arg)
      ));
  
      return Utils::IsValidArray(
        $IsMigrate
      );
    }
  
    static function GetEntityName(
      string $EntityClass
    ): string {
      $Entity = explode("\\", $EntityClass);

      return preg_replace(
        "/Entity$/", "", end($Entity)
      );
    }
  }
}