<?php

namespace Websyspro\Entity\Core\Bases;

use ReflectionClass;
use ReflectionProperty;
use Websyspro\Commons\Collection;
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
  ): Collection {
    $cacheAttrs = Util::where(
      self::$cacheAttrs[ static::class ],
      fn( Column $column ) => (
        $column->instance->attributeType === $attributeType
      )
    );

    foreach( $cacheAttrs as $attr ){
      $newCacheAttrs[ $attr->name ] = $attr;
    }

    return new Collection(
      $newCacheAttrs
    );
  }

  private static function getColumns(
  ): Collection {
    $columns = self::findByAttributeType( 
      AttributeType::column
    );

    $columns = array_merge(
      $columns->where( 
        fn( Column $column ) => Util::inArray( 
          $column->name, self::getEntityColumns()->initials 
        ) === true
      )->all(),
      $columns->where( 
        fn( Column $column ) => Util::inArray( 
          $column->name, self::getEntityColumns()->alls 
        ) === false
      )->all(),
      $columns->where( 
        fn( Column $column ) => Util::inArray( 
          $column->name, self::getEntityColumns()->ends 
        ) === true
      )->all()
    );

    return new Collection(
      Util::mapper(
        array_values( $columns ),
        fn( Column $column ) => $column->name
      )
    );
  }

  public static function getAttributes(
  ): mixed {
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

    return new EntityStructure(
      new Entity( class: static::class ),
      self::getColumns(),
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
}