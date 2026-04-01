<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\Statistics\Index;
use Websyspro\Entity\Enums\AttributeType;

class EntityStructure
{
  public array $types = [];
  public array $indexes = [];
  public array $uniques = [];  
  public array $foreigns = [];
  public array $primaryKey = [];
  public array $requireds = [];

  public function __construct(
    public Entity $entity,
    public array $columns = [],
    array $types = [],
    array $indexes = [],
    array $uniques = [],
    array $foreigns = [],
    array $primaryKey = [],
    array $requireds = []
  ){
    $this->defineTypes( $types );
    $this->definePrimaryKey( $primaryKey );
    $this->defineIndexes( $indexes );
    $this->defineUniques( $uniques );
    $this->defineForeigns( $foreigns );
    $this->defineRequireds( $requireds );
  }

  private function defineTypes(
    array $types = []
  ): void {
    foreach( $types as $type ){
      if( $type instanceof Column ){
        $this->types[ $type->name ] = $type;
      }
    }
  }

  private function getGroupName(
    array $columns,
    array $accumulates,
    AttributeType $attributeType
  ): array {
    if( empty( $columns )){
      return [];
    }

    foreach( $columns as $column ){
      if( $column instanceof Column ){
        if( $column->instance instanceof Index || $column->instance instanceof Unique ){
          if( isset( $column->instance->indexGroup )){
            $accumulates[ $column->instance->indexGroup ] = $column->name;
          } else
          if( isset( $column->instance->uniqueGroup )){
            $accumulates[ $column->instance->uniqueGroup ] = $column->name;
          }
        }
      }
    }

    foreach( $accumulates as $key => $accumulate ){
      $accumulates[ $key ] = sprintf(
        "%s_%s", match( $attributeType ){
          AttributeType::indexes => "Index", 
          AttributeType::uniques => "Unique"
        }, implode( "_", $accumulate ) 
      );
    }

    return $accumulates;
  }

  private function definePrimaryKey(
    array $primaryKey = []
  ): void {
    foreach( $primaryKey as $column ){
      if( $column instanceof Column ){
        $this->primaryKey[] = $column->name;
      }
    }
  }

  private function defineIndexes(
    array $indexes = []
  ): void {
    $this->indexes = $this->getGroupName( 
      $indexes, [], AttributeType::indexes
    );
  }

  private function defineUniques(
    array $uniques = []
  ): void {
    $this->uniques = $this->getGroupName( 
      $uniques, [], AttributeType::uniques
    );
  }

  private function defineForeigns(
    array $foreigns = []
  ): void {
    foreach( $foreigns as $column ){
      if( $column instanceof Column ){
        $foreignNew = new ForeignKey( $column );

        if( $foreignNew instanceof ForeignKey ){
          $this->foreigns[ $foreignNew->entity->class ] = $foreignNew;
        }
      }
    }
  }

  private function defineRequireds(
    array $requireds = []
  ): void {
    foreach( $requireds as $column ){
      if( $column instanceof Column ){
        $this->requireds[ $column->name ] = $column->name;
      }
    }
  }
}