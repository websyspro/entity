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
      $this->SetEntityCreatedIndexes();
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
      array $Propertys, 
     string $Key
    ): string {
      [ "type" => $type, 
        "autoinc" => $autoinc,
        "required" => $required,
      ] = $Propertys;

      return Utils::Join(
        [ "`{$Key}`"
          ,"{$type}", $this->IsNullable($required), $this->IsAutoInc($autoinc) 
        ], " "
      );
    }

    private function SetCreateds(
      string $Entity,
      array $Structure = [],
      array $StructureEntity = []
    ): void {
      if ( $this->HasEntityExists($Structure) === false ) {
        if( $Structure[EntityVersion::$New] instanceof EntityStructure ) {
          $StructureEntity = Utils::Mapper( $Structure[EntityVersion::$New]->ObterProperties(), 
            fn(array $Propertys, string $Key) => (
              $this->SetCreatedEntitys(
                Propertys: $Propertys, Key: $Key
              )
            )
          );

          $this->persistedArr[] = sprintf("create table `%s` (%s)",
            EntityUtils::ObterEntityName( $Entity ),
            Utils::Join( $StructureEntity, ", " )
          );
        }
      }
    }

    private function SetEntityCreated(
    ): void {
      Utils::Mapper( $this->Items, 
        fn(array $Structure, string $Entity) => $this->SetCreateds(
          Entity: $Entity, Structure: $Structure
        )
      );
    }

    private function SetCreatedIndexes(
      string $entity,
      array $structure = [],
      array $structureEntity = []
    ): void {
      if( $structure[EntityVersion::$New] instanceof EntityStructure ) {
        $structureEntity = Utils::Mapper($structure[EntityVersion::$New]->ObterConstraintIndexes(), 
          fn(array $propertys, string $key) => (
            Utils::Mapper($propertys, fn(array $indexArr) => ( 
              Utils::Join(array_merge(
                [ "Idx", EntityUtils::ObterEntityName($entity) ], $indexArr
              ), "_")
            ))
          )
        );

        print_r($structureEntity);
      }
    }

    private function SetEntityCreatedIndexes(
    ): void {
      Utils::Mapper( $this->Items, 
        fn(array $structure, string $entity) => (
          $this->SetCreatedIndexes(
            structure: $structure,
            entity: $entity
          )
        )
      );
    }
  }
}