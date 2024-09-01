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
      $this->SetEntityCreatedUniques();
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
      string $Entity,
      array $Structure = [],
      array $StructureEntitys = []
    ): void {
      if( $Structure[EntityVersion::$New] instanceof EntityStructure ) {
        $StructureEntitys = Utils::Mapper($Structure[EntityVersion::$New]->ObterConstraintIndexes(), 
          fn(array $propertys) => (
            Utils::Mapper($propertys, fn(array $indexArr) => ( 
              [ Utils::Join( array_merge(
                [ "Idx", EntityUtils::ObterEntityName($Entity) ], $indexArr
                ), "_"), Utils::Join( $indexArr, ", ")]
            ))
          )
        );

        Utils::Mapper($StructureEntitys, fn(array $StructureEntity ) => (
          Utils::Mapper($StructureEntity, fn($IndexesList) => (
            $this->persistedArr[] = call_user_func_array("sprintf", array_merge(
              [ "alter table `%s` add index `%s` (%s)", EntityUtils::ObterEntityName($Entity) ], $IndexesList
            ))
          ))
        ));
      }
    }

    private function SetEntityCreatedIndexes(
    ): void {
      Utils::Mapper( $this->Items, 
        fn(array $Structure, string $Entity) => (
          $this->SetCreatedIndexes(
            Structure: $Structure,
            Entity: $Entity
          )
        )
      );
    }

    private function SetCreatedUniques(
      string $Entity,
      array $Structure = [],
      array $StructureEntitys = []
    ): void {
      
    }    

    private function SetEntityCreatedUniques(
    ): void {
      Utils::Mapper( $this->Items, 
      fn(array $Structure, string $Entity) => (
        $this->SetCreatedUniques(
          Structure: $Structure,
          Entity: $Entity
        )
      )
    );;
    }
  }
}