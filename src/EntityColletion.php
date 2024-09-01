<?php

namespace Websyspro\Entity
{
  use Websyspro\Common\Utils;
  use Websyspro\Entity\Consts\EntityVersion;

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
      Utils::Mapper( $this->Items, fn($_, string $Entity) => (
        $this->Items[$Entity] = [
          EntityVersion::$Old => [],
          EntityVersion::$New => new EntityStructure( Entity: $Entity )
        ]
      ));
    }
  }
}