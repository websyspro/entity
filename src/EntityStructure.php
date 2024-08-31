<?php

namespace Websyspro\Entity
{
use Websyspro\Reflect\ClassReflectLoader;
  class EntityStructure
  {
    function __construct(
      public string $Entity,
      public ClassReflectLoader $Properties
    ){
      $this->SetEntityStructure();
    }

    function SetEntityStructure(
    ): void {
      $this->Properties = new ClassReflectLoader(
        objectOrClass: $this->Entity
      );
    }
  }
}