<?php

namespace Websyspro\Entity\Schemas;


use Websyspro\Entity\Decorators\Entity;
use Websyspro\Entity\Decorators\Index;
use Websyspro\Entity\Decorators\Synchronize;
use Websyspro\Entity\Decorators\Unique;
use Websyspro\Entity\Enums\EntityOrigin;
use Websyspro\Entity\Interfaces\EntityColumns;
use Websyspro\Entity\Interfaces\EntityNames;
use Websyspro\Entity\Types\ColumnList;
use ReflectionAttribute;
use ReflectionClass;
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
  public EntityColumns $initialDefaults;

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

  abstract protected function defineInitialDefaults(
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
    $this->initialDefaults = new EntityColumns();
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
  
  private function getEntityNames(
  ): void {
    $entityNames = $this->reflectionClass
      ->getAttributes( Entity::class );

    foreach( $entityNames as $entityName ){
      if( $entityName instanceof ReflectionAttribute ){
        $entityInstance = $entityName->newInstance();

        if( $entityInstance instanceof Entity ){
          $this->entityNames = new EntityNames(
            $entityInstance->alias,
            $entityInstance->alias
          );
        }
      }
    }
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
      }
    }

    $this->getGroupList( $this->indexes, Index::class );
    $this->getGroupList( $this->uniques, Unique::class );
  }
}