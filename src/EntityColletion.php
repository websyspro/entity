<?php

namespace Websyspro\Entity
{
  use Websyspro\Common\Utils;
  use Websyspro\Entity\Commons\EntityUtils;
  
  class EntityColletion
  {
    function __construct(
      public array $Items = []
    ){
      $this->Load();
    }
  
    private function Load(
    ): void {
      $this->SetEntityName();
      $this->SetEntityProperties();
      $this->SetEntityConstraints();
  
      print_r($this->Items);
    }
  
    private function SetEntityName(
    ): void {
      $this->Items = Utils::ArrayFlip(
        Utils::Mapper($this->Items, fn($EntityClass) => $EntityClass)
      );
    }
  
    private function SetEntityProperties(
    ): void {
      Utils::Mapper($this->Items, fn(mixed $_, string $Entity) => (
        $this->Items[$Entity] = new EntityStructure(
          Entity: $Entity
        )
      ));
    }
  
    private function SetEntityConstraints(
    ): void {}
  }
}