<?php

namespace Websyspro\Entity
{
  use Websyspro\Common\Utils;
  use Websyspro\Entity\Consts\ColumnOrder;
  use Websyspro\Entity\Consts\ConstraintType;
  use Websyspro\Reflect\ClassAttributs;
  use Websyspro\Reflect\ClassReflectLoader;
  
  class EntityStructure
  {
    private array $Properties  = [];
    private array $ConstraintIndexes = [];
    private array $ConstraintUniques = [];
    private array $ConstraintForeigns = [];

    function __construct(
      private string $Entity
    ){
      $this->SetEntityStructure();
      $this->SetEntityColumnOrder();
      $this->SetEntityConstraints();
      $this->SetEntityColumnRiquered();
      $this->SetEntitySaved();
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

    private function SetEntityConstraints(
    ): void {
      $this->SetEntityConstraintsIndexes();
      $this->SetEntityConstraintsUniques();
      $this->SetEntityConstraintsForeigns();
    }

    private function SetEntityConstraintsIndexes(
    ): void {
      Utils::Mapper( $this->Properties, function( array $propertys, string $key ){
        if (in_array( ConstraintType::$Index, array_keys( $propertys ))) {
          $this->ConstraintIndexes[$this->Entity][
            $propertys[ ConstraintType::$Index ]
          ][] = $key; 
        }
      });
    }

    private function SetEntityConstraintsUniques(
    ): void {
      Utils::Mapper( $this->Properties, function( array $propertys, string $key ){
        if (in_array( ConstraintType::$Unique, array_keys( $propertys ))) {
          $this->ConstraintUniques[$this->Entity][
            $propertys[ ConstraintType::$Unique ]
          ][] = $key; 
        }
      });      
    }

    private function SetEntityConstraintsForeigns(
    ): void {
      Utils::Mapper( $this->Properties, function( array $propertys, string $key ){
        if (in_array( ConstraintType::$Foreign, array_keys( $propertys ))) {
          $this->ConstraintForeigns[] = $propertys; 
        }
      });
    }

    private function SetEntityColumnRiquered(
    ): void {
      Utils::Mapper( $this->Properties, function( array $propertys, string $key ){
        $this->Properties[ $key ] = [
          ConstraintType::$Type => $propertys[
            ConstraintType::$Type
          ],
          ConstraintType::$Required => isset(
            $propertys[ ConstraintType::$Required ]
          ) ?? false 
        ];
      });
    }

    private function SetEntitySaved(
    ): void {}
  }
}