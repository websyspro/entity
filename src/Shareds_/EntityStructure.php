<?php

namespace Websyspro\Entity\Shareds_;

use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\Statistics\Index;
use Websyspro\Entity\Decorations\ColumnName;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\Entity;
use ReflectionAttribute;
use Websyspro\Entity\Enums\MetaType;
use Websyspro\Entity\Interfaces\ItemForeignKey;

class EntityStructure
{
  public Entity $entity;
  public array $columns = [];
  public array $alias = [];
  public array $types = [];
  public array $indexes = [];
  public array $uniques = [];  
  public array $foreigns = [];
  public array $primaryKey = [];
  public array $requireds = [];
  public array $autoIncrements = [];

  public function __construct(
    Entity $entity,
    array $columns = [],
    array $types = [],
    array $alias = [],
    array $indexes = [],
    array $uniques = [],
    array $foreigns = [],
    array $primaryKey = [],
    array $requireds = [],
    array $autoIncrements = []
  ){
    $this->defineEntity( $entity );
    $this->defineColumns( $columns );
    $this->defineAlias( $alias );
    $this->defineTypes( $types );
    $this->definePrimaryKey( $primaryKey );
    $this->defineIndexes( $indexes );
    $this->defineUniques( $uniques );
    $this->defineForeigns( $foreigns );
    $this->defineRequireds( $requireds );
    $this->defineAutoIncrements( $autoIncrements );
  }

  private function defineEntity(
    Entity $entity
  ): void {
    $this->entity = $entity;
  }  

  private function defineColumns(
    array $columns = [],
  ): void {
    $this->columns = $columns;
  }

  private function defineAlias(
    array $alias = [],
  ): void {
    foreach( $alias as $columnName => $alia ){
      if( $alia instanceof ColumnName ){
        $this->alias[ $columnName ] = $alia->columnName;
      }
    }
  }  

  private function defineTypes(
    array $types = []
  ): void {
    $this->types = $types;
  }

  private function getGroupName(
    array $columns,
    array $accumulates,
    AttributeType $attributeType
  ): array {
    if( sizeof( $columns ) === 0 ){
      return [];
    }

    foreach( $columns as $columnName => $column ){
      if( $column instanceof Index ){
        if( isset( $column->indexGroup )){
          $accumulates[ $column->indexGroup ][] = $columnName;
        } else
        if( $column instanceof Unique ){
          $accumulates[ $column->uniqueGroup ][] = $columnName;
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
    array $primaryKeys = []
  ): void {
    foreach( $primaryKeys as $columnName => $primaryKey ){
      if( $primaryKey instanceof ReflectionAttribute ){
        $this->primaryKey[ $columnName ] = $columnName;
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
    foreach( $foreigns as $key => $foreignKey ){
      if( $foreignKey instanceof ForeignKey ){
        if( class_exists( $foreignKey->entityReference )){
          $entityStructure = call_user_func_array(
            [ $foreignKey->entityReference, "meta" ], [ MetaType::Query ]
          );    
          
          if( $entityStructure instanceof EntityStructure ){
            $referenteTable = $entityStructure->entity->table;
            $referenteKey = reset( $entityStructure->primaryKey );

            $this->foreigns[ $foreignKey->entityReference ] = new ItemForeignKey(
              $this->entity->table, $key, $referenteTable, $referenteKey
            );
          }
        }        
      }
    }
  }

  private function defineRequireds(
    array $requireds = []
  ): void {
    foreach( $requireds as $columnName => $required ){
      if( $required instanceof ReflectionAttribute ){
        $this->requireds[ $columnName ] = $columnName;
      }
    }
  }

  private function defineAutoIncrements(
    array $autoIncrements = []
  ): void {
    foreach( $autoIncrements as $columnName => $autoIncrement ){
      if( $autoIncrement instanceof ReflectionAttribute ){
        $this->autoIncrements[ $columnName ] = $columnName;
      }
    }
  }
}