<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\Statistics\Index;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Commons\Util;

class EntityStructure
{
  public function __construct(
    public Entity $entity,
    public Collection $columns,
    public Collection $types,
    public Collection $indexes,
    public Collection $uniques,
    public Collection $foreigns,
    public Collection $primaryKey,
    public Collection $requireds,
    public Collection $oneToMany,
    public Collection $oneToOne
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
    Collection $columns,
    AttributeType $attributeType,
    Collection $groupNameList = new Collection()
  ): Collection {
    $columns = $columns->reduce(
      [], function( array|null $acc, Column $column ) {
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

    $columns = $columns->mapper(
      fn( array $indexGroup ) => Util::sprintFormat(
        "%s_%s", [ match( $attributeType ){
          AttributeType::indexes => "Index", 
          AttributeType::uniques => "Unique"
        }, Util::join( "_",  $indexGroup ) ]
      )
    );

    $columns->mapper(
      fn( string $groupName ) => (
        $groupNameList->add( $groupName, $groupName )
      )
    );

    return $groupNameList;
  }

  private function definePrimaryKey(
  ): void {
    if( $this->primaryKey->exist() ){
      $this->primaryKey = $this->primaryKey->mapper(
        fn( Column $column ) => new PrimaryKey($column->name)
      );
    }
  }

  private function defineIndexes(
  ): void {
    $this->indexes = $this->getGroupName( 
      $this->indexes, AttributeType::indexes
    );
  }

  private function defineUniques(
  ): void {
    $this->uniques = $this->getGroupName( 
      $this->uniques, AttributeType::uniques
    );
  }

  private function defineForeigns(
  ): void {
    $this->foreigns = $this->foreigns->mapper(
      fn( Column $column ) => new ForeignKey( 
        $column, $this
      )
    );
  }

  private function defineRequireds(
  ): void {
    $this->requireds = $this->requireds->mapper(
      fn( Column $column ) => $column->name
    );
  }

  private function defineOneToMany(
  ): void {
    $this->oneToMany = $this->oneToMany->mapper(
      fn( Column $column ) => new Entity(
        $column->instance->entityReference
      )
    );
  }

  private function defineOneToOne(
  ): void {
    $this->oneToOne = $this->oneToOne->mapper(
      fn( Column $column ) => new Entity(
        $column->instance->entityReference
      )
    );
  }  
}