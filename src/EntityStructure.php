<?php

namespace Websyspro\Entity
{
  use Websyspro\Common\Utils;
  use Websyspro\Entity\Consts\ColumnOrder;
  use Websyspro\Reflect\ClassAttributs;
  use Websyspro\Reflect\ClassReflectLoader;
  
  class EntityStructure
  {
    private array $Properties  = [];

    function __construct(
      private string $Entity
    ){
      $this->SetEntityStructure();
      $this->SetEntityColumnOrder();
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

    private function SetEntityColumnOrder(
    ): void {
      $this->Properties = array_merge(
        Utils::Filter( $this->Properties, fn($_, string $key) =>  in_array( $key, ColumnOrder::$Header)),
        Utils::Filter( $this->Properties, fn($_, string $key) => !in_array( $key, ColumnOrder::$Body)),
        Utils::Filter( $this->Properties, fn($_, string $key) =>  in_array( $key, ColumnOrder::$Footer))
      );
    }
  }
}