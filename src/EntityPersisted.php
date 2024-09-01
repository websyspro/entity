<?php

namespace Websyspro\Entity
{
use Websyspro\Common\Utils;
    use Websyspro\Entity\Consts\ConstraintType;
    use Websyspro\Entity\Consts\EntityVersion;

  class EntityPersisted
  {
    private array $persistedArr = [];

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

    private function IsNullable(
      bool $required = true
    ): string {
      return $required ? "not null" : "null";
    }

    private function IsAutoInc(
      bool $autoinc = true
    ): string {
      return $autoinc ? "primary key auto_increment" : "";
    }

    private function SetCreatedEntitys(
      array $propertys, 
     string $key
    ): string {
      [ "type" => $type, 
        "autoinc" => $autoinc,
        "required" => $required,
      ] = $propertys;

      return trim( "`{$key}` {$type} {$this->IsNullable($required)} {$this->IsAutoInc($autoinc)}");
    }

    private function SetCreateds(
      string $entity,
      array $structure = [],
      array $structureEntity = []
    ): void {
      if ( $this->HasEntityExists($structure) === false ) {
        if( $structure[EntityVersion::$New] instanceof EntityStructure ) {
          $structureEntity = Utils::Mapper( $structure[EntityVersion::$New]->ObterProperties(), 
            fn(array $propertys, string $key) => (
              $this->persistedArr[] = $this->SetCreatedEntitys(
                propertys: $propertys, key: $key
              )
            )
          );

          print_r($structureEntity);
        }
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