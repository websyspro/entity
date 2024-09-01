<?php

namespace Websyspro\Entity
{
use Websyspro\Common\Utils;
    use Websyspro\Entity\Consts\ConstraintType;
    use Websyspro\Entity\Consts\EntityVersion;

  class EntityPersisted
  {
    function __construct(
      private array $Items = [] 
    ){
      $this->SetEntityCreateds();
    }

    private function HasEntityExists(
      array $structure = []
    ): bool {
      return Utils::IsValidArray( $structure[EntityVersion::$Old])
          && Utils::IsEmptyArray( $structure[EntityVersion::$Old]);
    }

    private function SetCreateds(
      string $entity,
      array $structure = []
    ): void {
      if ( $this->HasEntityExists($structure) === false ) {
        print_r($structure[EntityVersion::$New]);
      }
    }

    private function SetEntityCreateds(
    ): void {
      Utils::Mapper( $this->Items, 
        fn(array $structure, string $entity) => $this->SetCreateds(
          entity: $entity, structure: $structure
        )
      );
    }
  }
}