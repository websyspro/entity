<?php

namespace Websyspro\Entity\Core\Bases;

use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\Entity;
use Websyspro\Entity\Shareds\Column;
use ReflectionAttribute;
use ReflectionProperty;
use ReflectionClass;
use Websyspro\Entity\Shareds\AbstractColumn;

class AbstractEntity
{
  protected static ReflectionClass $cacheReflectionClassBase;
  protected static array $cacheReflectionClass = [];
  protected static array $cacheColumnsBase = [];
  protected static array $cacheColumns = [];
  protected static array $cacheAttributes = [];
  protected static array $cacheAttributesByType = [];
  protected static array $cacheForeignKeys = [];


  private static function getColumnsBaseStarts(
  ): array {
    return array_filter( 
      self::$cacheColumnsBase, fn( string $column ) => (
        $column === reset( self::$cacheColumnsBase )
      )
    );
  }

  private static function getColumsCenters(
  ): array {
    return array_filter( 
      self::$cacheColumns[ static::class ][ AttributeType::column->name ], fn(string $column) => (
        $column !== reset( self::$cacheColumnsBase ) || in_array( 
          $column, self::$cacheColumnsBase 
        ) === false
      )
    );
  }  

  private static function getColumnsBaseEnds(
  ): array {
    return array_filter( 
      self::$cacheColumnsBase, fn(string $column) => (
        $column !== reset( self::$cacheColumnsBase )
      )
    );
  }  

  private static function getColumns(
  ): array {
    if (empty( self::$cacheColumnsBase )) {
      self::$cacheColumnsBase = array_map(
        fn( ReflectionProperty $column ) => $column->name, 
          self::$cacheReflectionClassBase->getProperties(
            ReflectionProperty::IS_PUBLIC
          )
      );
    }
    
    if (empty( self::$cacheColumns[ static::class ][ AttributeType::column->name ])) {
      self::$cacheColumns[ static::class ][ AttributeType::column->name ] = array_map(
        fn( ReflectionProperty $column ) => $column->name,
          self::$cacheReflectionClass[ static::class ]->getProperties(
            ReflectionProperty::IS_PUBLIC
          )
      );

      self::$cacheColumns[ static::class ][ AttributeType::column->name ] = array_merge(
        self::getColumnsBaseStarts(), self::getColumsCenters(), self::getColumnsBaseEnds()
      );
    }

    return self::$cacheColumns[ static::class ][ AttributeType::column->name ];
  }

  private static function getColumnByType(
    string $columnType
  ): array {
    if (empty(self::$cacheAttributes[ static::class ])) {
      $propertys = self::$cacheReflectionClass[ static::class ]
        ->getProperties(ReflectionProperty::IS_PUBLIC);

      foreach ($propertys as $property) {
        foreach ($property->getAttributes() as $attribute) {
          self::$cacheAttributes[ static::class ][] = new Column(
            $property->getName(),
            $attribute->getName(),
            $attribute
          );
        }
      }
    }

    if( isset(self::$cacheAttributesByType[ static::class ][ $columnType ]) === false ){
      self::$cacheAttributesByType[ static::class ][ $columnType ] = [];

      foreach( self::$cacheAttributes[ static::class ] as $column ){
        if( $column instanceof Column && $column->columnType === $columnType ){
          if( $column->instance instanceof ReflectionAttribute ){
            $column->instance = $column->instance->newInstance();
          }

          self::$cacheAttributesByType[ static::class ][ $columnType ][] = $column;
        }
      }
    }

    return self::$cacheAttributesByType[ static::class ][ $columnType ];
  } 

  private static function getTypes(
  ): array {
    return self::getColumnByType( AbstractColumn::class );
  }  

  private static function getForeignKeys(
  ): array {
    return self::getColumnByType( ForeignKey::class );
  }

  private static function getPrimaryKeys(
  ): array {
    return self::getColumnByType( PrimaryKey::class );
  }  

  public static function meta(
  ): mixed {
    if ( isset( self::$cacheReflectionClassBase ) === false){
      self::$cacheReflectionClassBase = new ReflectionClass( BaseEntity::class );
    }

    if ( isset( self::$cacheReflectionClass[ static::class ]) === false ) {
      self::$cacheReflectionClass[ static::class ] = new ReflectionClass( static::class );
    }

    return new EntityStructure(
      new Entity( static::class ),
      self::getColumns(),
      [],
      [],
      [],
      self::getForeignKeys(),
      self::getPrimaryKeys(),
      [],
      [],
      [] 
    );
  }
}