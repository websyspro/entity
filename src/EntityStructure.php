<?php

namespace Websyspro\Entity
{
  use Websyspro\Common\Utils;
  use Websyspro\Reflect\ClassAttributs;
  use Websyspro\Reflect\ClassReflectLoader;
  
  class EntityStructure
  {
    private array $Properties  = [];

    function __construct(
      private string $Entity
    ){
      $this->SetEntityStructure();
    }

    private function ObterEntityStructure(
    ): ClassReflectLoader {
      return new ClassReflectLoader(
        objectOrClass: $this->Entity
      );
    }

    private function SetEntityStructure(
    ): void {
      Utils::Mapper( $this->ObterEntityStructure()->ObterProperties(), fn( array $Properties, string $name ) => (
        $this->Properties[ $name ] = call_user_func_array(
          "array_merge", Utils::Mapper( 
            $Properties, fn( ClassAttributs $Property) => $Property->New()->Execute()
          )
        )
      ));
    }
  }
}