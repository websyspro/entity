<?php

namespace Websyspro\Entity\Schemas;


use Websyspro\Connection\Database;
use Websyspro\Connection\Enums\DriverType;
use Websyspro\Entity\Decorators\Entity;
use Websyspro\Entity\Decorators\Index;
use Websyspro\Entity\Decorators\Synchronize;
use Websyspro\Entity\Decorators\Unique;
use Websyspro\Entity\Enums\EntityOrigin;
use Websyspro\Entity\Interfaces\EntityColumns;
use Websyspro\Entity\Interfaces\EntityNames;
use Websyspro\Entity\Interfaces\ForeignKeyStructure;
use Websyspro\Entity\Types\ColumnAbstract;
use Websyspro\Entity\Types\ColumnAutoIncrement;
use Websyspro\Entity\Types\ColumnBigInt;
use Websyspro\Entity\Types\ColumnDecimal;
use Websyspro\Entity\Types\ColumnDouble;
use Websyspro\Entity\Types\ColumnFlag;
use Websyspro\Entity\Types\ColumnFloat;
use Websyspro\Entity\Types\ColumnInt;
use Websyspro\Entity\Types\ColumnList;
use ReflectionAttribute;
use ReflectionClass;
use Websyspro\Entity\Types\ColumnSmallInt;
use function array_slice;
use function sprintf;

abstract class AbstractEntityStructure
{
  public bool $synchronize;
  public EntityNames $entityNames;
  public EntityColumns $columns;
  public EntityColumns $alias;
  public EntityColumns $types;
  public EntityColumns $lengths;
  public EntityColumns $precisions;
  public EntityColumns $requireds;
  public EntityColumns $primaryKeys;
  public EntityColumns $generateds;
  public EntityColumns $indexes;
  public EntityColumns $uniques;
  public EntityColumns $foreignKeys;
  public EntityColumns $entityLists;
  public EntityColumns $initialInsertDefaults;
  public EntityColumns $initialUpdateDefaults;
  public EntityColumns $initialDeleteDefaults;

  public function __construct(
    public readonly ReflectionClass $reflectionClass
  ){
    $this->start();
  }

  abstract protected function defineTypes(
    string $name,
    string $type
  ): void;

  abstract protected function defineGenerateds(
    string $name,
    string $type
  ): void; 
  
  abstract protected function defineAlias(
    string $name,
    mixed $instance
  ): void;

  abstract protected function defineLengths(
    string $name,
    mixed $instance
  ): void;
  
  abstract protected function definePrecisions(
    string $name,
    mixed $instance
  ): void;
  
  abstract protected function defineRequireds(
    string $name,
    mixed $instance
  ): void;  

  abstract protected function definePrimaryKeys(
    string $name,
    mixed $instance
  ): void;

  abstract protected function defineIndexes(
    string $name,
    mixed $instance
  ): void;

  abstract protected function defineUniques(
    string $name,
    mixed $instance
  ): void;
  
  abstract protected function defineForeignKeys(
    string $name,
    mixed $instance
  ): void;

  abstract protected function defineEntityList(
    string $name,
    mixed $instance
  ): void;

  abstract protected function defineInitialInsertDefaults(
    string $name,
    mixed $instance
  ): void;
  
  abstract protected function defineInitialUpdateDefaults(
    string $name,
    mixed $instance
  ): void;
  
  abstract protected function defineInitialDeleteDefaults(
    string $name,
    mixed $instance
  ): void; 

  private function start(
  ): void {
    $this->getInitialArgs();
    $this->getSynchronize();
    $this->getEntityNames();
    $this->getEntityColumns();
    $this->getEntityColumnsAttrs();
  }

  private function getInitialArgs(
  ): void {
    $this->columns = new EntityColumns();
    $this->alias = new EntityColumns();
    $this->types = new EntityColumns();
    $this->lengths = new EntityColumns();
    $this->precisions = new EntityColumns();
    $this->requireds = new EntityColumns();
    $this->primaryKeys = new EntityColumns();
    $this->generateds = new EntityColumns();
    $this->indexes = new EntityColumns();
    $this->uniques = new EntityColumns();
    $this->foreignKeys = new EntityColumns();
    $this->entityLists = new EntityColumns();
    $this->initialInsertDefaults = new EntityColumns();
    $this->initialUpdateDefaults = new EntityColumns();
    $this->initialDeleteDefaults = new EntityColumns();
  }

  private function getSynchronize(
  ): void {
    $synchronizeds = $this->reflectionClass
      ->getAttributes( Synchronize::class );

    foreach( $synchronizeds as $synchronized ){
      if( $synchronized instanceof ReflectionAttribute ){
        $synchronizedInstance = $synchronized->newInstance();

        if( $synchronizedInstance instanceof Synchronize ){
          $this->synchronize = $synchronizedInstance->enable;
        }
      }
    }
    
    if(isset( $this->synchronize ) === false){
      $this->synchronize = true;
    }
  }

  public function isColumnType(
    string $type
  ): bool {
    return is_subclass_of( 
      $type, ColumnAbstract::class
    );
  }  

  private function getEntityShortNames(
    ReflectionClass $reflectionClass
  ): string {
    return preg_replace(
      "#Entity#", "", 
        $reflectionClass->getShortName()
    );
  }

  public function getEntityNamesByEntity(
    ReflectionClass $reflectionClass,
    EntityNames|null $entityNames = null
  ): EntityNames {
    $entityNamesList = $reflectionClass
      ->getAttributes( Entity::class );
    
    if( empty( $entityNamesList ) === false ){
      foreach( $entityNamesList as $entityName ){
        if( $entityName instanceof ReflectionAttribute ){
          $entityInstance = $entityName->newInstance();

          if( $entityInstance instanceof Entity ){
            $entityNames = new EntityNames(
              $this->getEntityShortNames( $reflectionClass ), $entityInstance->alias
            );
          }
        }
      }
    } else {
      $entityNames = new EntityNames(
        $this->getEntityShortNames( $reflectionClass ),
        $this->getEntityShortNames( $reflectionClass )
      );      
    }

    return $entityNames;    
  }

  public function getEntityNames(
  ): void {
    $this->entityNames = $this->getEntityNamesByEntity(
      $this->reflectionClass
    );
  }

  private function getEntityColumns(
    array $propertys = [],
  ): void {
    foreach( $this->reflectionClass->getProperties() as $property ){
      if( $property->getType()->getName() === ColumnList::class ){
        continue;
      }

      $propertys[
        $this->reflectionClass->getName() === $property->class 
          ? EntityOrigin::Self->value : EntityOrigin::Base->value
      ][] = $property->getName();
    }

    $this->columns = new EntityColumns([
      ...array_slice( $propertys[ EntityOrigin::Base->value ] ?? [], 0, 1 ),
      ...array_slice( $propertys[ EntityOrigin::Self->value ] ?? [], 0 ),
      ...array_slice( $propertys[ EntityOrigin::Base->value ] ?? [], 1 ),
    ]);
  }

  private function setColsList(
    array $types
  ): void {
    $this->columns->items = array_values(
      array_intersect( 
        $this->columns->items, 
          array_keys( $types )
      )
    );
  }

  private function getGroupList(
    EntityColumns $entityColumns,
    string $type
  ): void {
    foreach( $entityColumns->items as $group => $columns ){
      $entityColumns->items[$group] = sprintf(
        $type === Index::class
          ? "IDX_%s_%s" : "UNQ_%s_%s", $this->entityNames->alias, implode( "_", $columns )
      );
    }

    $entityColumns->items = array_values(
      array_map( fn(string $name) => (
        Database::driver() === DriverType::Postgres 
          ? mb_strtolower($name) : $name
      ), $entityColumns->items )
    );    
  }

  private function getEntityColumnsAttrs(
  ): void {
    foreach( $this->reflectionClass->getProperties() as $property ){
      $propertyName = $property->getName();
      $propertyType = $property->getType()->getName();

      if( $propertyName === ColumnList::class ){
        continue;
      }

      $this->defineTypes( $propertyName, $propertyType );
      $this->defineGenerateds( $propertyName, $propertyType );
      
      foreach( $property->getAttributes() as $attribute ){
        $instance = $attribute->newInstance();

        $this->defineAlias( $propertyName, $instance );
        $this->defineLengths( $propertyName, $instance );
        $this->defineRequireds( $propertyName, $instance );
        $this->definePrecisions( $propertyName, $instance );
        $this->definePrimaryKeys( $propertyName, $instance );
        $this->defineIndexes( $propertyName, $instance );
        $this->defineUniques( $propertyName, $instance );
        $this->defineForeignKeys( $propertyName, $instance );
        $this->defineEntityList( $propertyName, $instance );
        $this->defineInitialInsertDefaults( $propertyName, $instance );
        $this->defineInitialUpdateDefaults( $propertyName, $instance );
        $this->defineInitialDeleteDefaults( $propertyName, $instance );
      }
    }
    
    $this->setColsList( $this->types->items );
    $this->getGroupList( $this->indexes, Index::class );
    $this->getGroupList( $this->uniques, Unique::class );
  }

  public function numericColumnsType(
  ): array {
    return [
      ColumnInt::class,
      ColumnBigInt::class,
      ColumnSmallInt::class,
      ColumnAutoIncrement::class,
      ColumnFloat::class,
      ColumnDouble::class,
      ColumnDecimal::class,
      ColumnFlag::class,
    ];
  }  

  public function getEntityAlias(
  ): string {
    return $this->entityNames->alias;
  }

  public function getColumnName(
    string $column
  ): string {
    return $this->columns->items[
      array_search( $column, $this->columns->items )
    ];
  } 
  
  public function getColumnType(
    string $column
  ): string {
    return $this->types->items[$column];
  }  

  public function getPrimaryKey(
  ): string|null {
    [ $columnPrimaryKey ] = array_values(
      $this->primaryKeys->items
    );

    if( isset($this->requireds->items[ $columnPrimaryKey ]) === false ){
      return null;
    }

    if( isset($this->generateds->items[ $columnPrimaryKey ]) === false ){
      return null;
    }    

    return $columnPrimaryKey;
  }

  public function getColumnForeingKeyByAlias(
    string $alias
  ): string|null {
    foreach( $this->foreignKeys->items as $primaryKey => $foreignKey ){
      if( $foreignKey instanceof ForeignKeyStructure ){
        if( $foreignKey->entity->alias === $alias ){
          return $primaryKey;
        }
      }
    }

    return null;
  }

  public function getColumnsSelecteds(
  ): array {
    return array_map(
      fn( string $column ) => sprintf(
        "%s.%s As %s_%s", 
        $this->getEntityAlias(), $column, 
        $this->getEntityAlias(), $column
      ), $this->columns->items
    );
  } 
}