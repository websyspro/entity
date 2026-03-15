<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\Statistics\Index;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Commons\Util;

class EntityStructure
{
  public function __construct(
    public Entity $entity,
    public array $columns,
    public array $types,
    public array $indexes,
    public array $uniques,
    public array $foreigns,
    public array $primaryKey,
    public array $requireds,
    public array $oneToMany,
    public array $oneToOne
  ){
    $this->definePrimaryKey();
    $this->defineIndexes();
    $this->defineUniques();
    $this->defineForeigns();
    $this->defineRequireds();
    $this->defineOneToMany();
    $this->defineOneToOne();
  }

  private function getGroupName(
    array $columns,
    array $groupNameList = [],
    AttributeType|null $attributeType = null
  ): array {
    $columns = array_reduce(
      $columns, function( array|null $acc, Column $column ) {
        if( $column->instance instanceof Index || $column->instance instanceof Unique ){
          if( isset( $column->instance->indexGroup )){
            $acc[ $column->instance->indexGroup ][] = $column->name; 
          } else
          if( isset( $column->instance->uniqueGroup )){
            $acc[ $column->instance->uniqueGroup ][] = $column->name;
          }
        } 

        return $acc;
      }
    );

    $columns = array_map(
      fn( array $indexGroup ) => Util::sprintFormat(
        "%s_%s", [ match( $attributeType ){
          AttributeType::indexes => "Index", 
          AttributeType::uniques => "Unique"
        }, Util::join( "_",  $indexGroup ) ]
      ), $columns ?? []
    );

    if( Util::sizeArray( $columns ) === 0 ){
      return $groupNameList;
    }
      
    foreach( $columns as $groupName  ){
      $groupNameList[ $groupName ] = $groupName;
    }

    return $groupNameList;
  }

  private function definePrimaryKey(
  ): void {
    if( Util::sizeArray( $this->primaryKey ) !== 0 ){
      $this->primaryKey = array_map(
        fn( Column $column ) => new PrimaryKey($column->name), $this->primaryKey
      );
    }
  }

  private function defineIndexes(
  ): void {
    $this->indexes = $this->getGroupName( 
      $this->indexes, [], AttributeType::indexes
    );
  }

  private function defineUniques(
  ): void {
    $this->uniques = $this->getGroupName( 
      $this->uniques, [], AttributeType::uniques
    );
  }

  private function defineForeigns(
  ): void {
    $this->foreigns = array_map(
      fn( Column $column ) => new ForeignKey( $column, $this ), $this->foreigns
    );
  }

  private function defineRequireds(
  ): void {
    $this->requireds = array_map(
      fn( Column $column ) => $column->name, $this->requireds
    );
  }

  private function defineOneToMany(
  ): void {
    $this->oneToMany = array_map(
      fn( Column $column ) => new Entity(
        $column->instance->entityReference
      ), $this->oneToMany
    );
  }

  private function defineOneToOne(
  ): void {
    $this->oneToOne = array_map(
      fn( Column $column ) => new Entity(
        $column->instance->entityReference
      ), $this->oneToOne
    );
  }  
}