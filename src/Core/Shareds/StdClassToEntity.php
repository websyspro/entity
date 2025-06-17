<?php

namespace Websyspro\Entity\Core\Shareds;

class StdClassToEntity
{
  public static function Parse(
    array|object $stdClass,
    string $entity
  ): object {
    $newEntity = (
      new $entity
    );

    foreach($stdClass as $key => $val){
      $newEntity->{$key} = $val;
    }
    
    return $newEntity;
  }
}