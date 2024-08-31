<?php

namespace Websyspro\Entity
{
use Websyspro\Reflect\ClassReflectLoader;
  class EntityStructure
  {
    function __construct(
      public string $Entity,
      public array $Properties = []
    ){
      $this->SetEntityStructure();
    }

    function SetEntityStructure(
    ): void {
      $ClassReflectLoader = new ClassReflectLoader(
        objectOrClass: $this->Entity
      );

      print_r($ClassReflectLoader);
    }
  }
}