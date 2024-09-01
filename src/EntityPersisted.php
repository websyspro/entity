<?php

namespace Websyspro\Entity
{
  use Websyspro\Common\Utils;
  use Websyspro\Entity\Commons\EntityUtils;
  use Websyspro\Entity\Consts\EntityVersion;

  class EntityPersisted
  {
    private array $persistedArr = [];

    function __construct(
      private array $Items = [] 
    ){
      $this->SetEntityCreated();
      $this->SetentityCreatedIndexes();
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

      return Utils::Join(
        [ "`{$key}`"
          ,"{$type}", $this->IsNullable($required), $this->IsAutoInc($autoinc) 
        ], " "
      );
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
              $this->SetCreatedEntitys(
                propertys: $propertys, key: $key
              )
            )
          );

          $this->persistedArr[] = sprintf("create table `%s` (%s)",
            EntityUtils::GetEntityName( $entity ),
            Utils::Join( $structureEntity, ", " )
          );
        }
      }
    }

    private function SetEntityCreated(
    ): void {
      Utils::Mapper( $this->Items, 
        fn(array $structure, string $entity) => $this->SetCreateds(
          entity: $entity, structure: $structure
        )
      );
    }

    private function SetentityCreatedIndexes(
    ): void {
      print_r($this->Items);
    }
  }
}