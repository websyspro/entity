<?php

namespace Websyspro\Entity\Core\Bases;

use ReflectionClass;
use ReflectionProperty;
use Websyspro\Commons\Util;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\Column;
use Websyspro\Entity\Shareds\Entity;
use Websyspro\Entity\Shareds\EntityColumns;
use Websyspro\Entity\Shareds\EntityStructure;

class AbstractEntity
{
  protected static array $cacheAttrs = [];
  protected static array $cacheColumns = [];
  protected static array $cacheEntityStructure = [];
  private static function getEntityColumns(
  ): EntityColumns {
    if( Util::sizeArray( self::$cacheColumns ) === 0 ){
      $reflectionClass = new ReflectionClass(
        BaseEntity::class
      );

      self::$cacheColumns = Util::mapper(
        $reflectionClass->getProperties( ReflectionProperty::IS_PUBLIC ),
        fn( ReflectionProperty $property ) => $property->getName() 
      );
    }

    return new EntityColumns(
      Util::where( 
        self::$cacheColumns, 
        fn( string $column ) => (
          $column === reset( self::$cacheColumns )
        )
      ),
      Util::where( 
        self::$cacheColumns, 
        fn( string $column ) => (
          $column !== reset( self::$cacheColumns )
        )
      ),
      self::$cacheColumns
    );
  }

  private static function findByAttributeType(
    AttributeType $attributeType,
    array $newCacheAttrs = []
  ): array {
    $cacheAttrs = Util::where(
      self::$cacheAttrs[ static::class ],
      fn( Column $column ) => (
        $column->instance->attributeType === $attributeType
      )
    );

    foreach( $cacheAttrs as $attr ){
      if( in_array( $attributeType, [ AttributeType::foreigns ] )){
        $newCacheAttrs[] = $attr;
      } else {
        $newCacheAttrs[ $attr->name ] = $attr;
      }
    }

    return $newCacheAttrs;
  }

  private static function findByAttributeColumns(
  ): array {
    $columns = self::findByAttributeType( 
      AttributeType::column
    );
    
    $columns = array_merge(
      array_filter( 
        $columns, fn( Column $column ) => Util::inArray( 
          $column->name, self::getEntityColumns()->initials 
        ) === true 
      ),
      array_filter( 
        $columns, fn( Column $column ) => Util::inArray( 
          $column->name, self::getEntityColumns()->alls 
        ) === false
      ),
      array_filter( 
        $columns, fn( Column $column ) => Util::inArray( 
          $column->name, self::getEntityColumns()->ends 
        ) === true
      )
    );

    return array_map(
      fn( Column $column ) => $column->name, array_values( $columns )
    );
  }

  public static function getAttributes(
  ): mixed {
    if( isset( self::$cacheAttrs[ static::class ] ) === false ){
      $reflectionClass = new ReflectionClass(
        static::class
      );

      foreach( $reflectionClass->getProperties( ReflectionProperty::IS_PUBLIC ) as $property ){
        foreach( $property->getAttributes() as $attribute ){
          self::$cacheAttrs[ static::class ][] = new Column(
            $property->getName(), $attribute->newInstance()
          );
        }
      }
    }

    if( isset( self::$cacheEntityStructure[ static::class ] ) === false ){
      self::$cacheEntityStructure[ static::class ] = new EntityStructure(
        new Entity( static::class ),
        self::findByAttributeColumns(),
        self::findByAttributeType( AttributeType::column ),
        self::findByAttributeType( AttributeType::indexes ),
        self::findByAttributeType( AttributeType::uniques ),
        self::findByAttributeType( AttributeType::foreigns ),
        self::findByAttributeType( AttributeType::primaryKey ),
        self::findByAttributeType( AttributeType::requireds ),
        self::findByAttributeType( AttributeType::oneToMany ),
        self::findByAttributeType( AttributeType::oneToOne )
      );
    }

    return self::$cacheEntityStructure[ static::class ];
  }
}