<?php

namespace Websyspro\Entity
{
use Websyspro\Reflect\ClassReflectLoader;
  class EntityStructure
  {
    public ClassReflectLoader $Properties;

    function __construct(
      public string $Entity
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