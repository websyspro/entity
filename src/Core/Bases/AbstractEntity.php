<?php

namespace Websyspro\Entity\Core\Bases;

use Websyspro\Entity\Interfaces\IAbstractColumn;
use Websyspro\Entity\Interfaces\IColumnsDefault;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IColumn;
use Websyspro\Entity\Interfaces\IEntity;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionProperty;
use ReflectionClass;

class AbstractEntity
{
  protected static array $columnsDefaultCache = [];
  protected static array $attributesCache = [];

  public static function getColumnsDefault(
  ): IColumnsDefault {
    if( Util::sizeArray( self::$columnsDefaultCache ) === 0){
      $reflectionClass = new ReflectionClass(
        BaseEntity::class
      );
  
      self::$columnsDefaultCache = Util::mapper(
        $reflectionClass->getProperties( ReflectionProperty::IS_PUBLIC ),
        fn( ReflectionProperty $property ) => $property->getName() 
      );
    }

    return new IColumnsDefault(
      Util::where( self::$columnsDefaultCache, fn( string $column ) => $column === reset( self::$columnsDefaultCache )),
      Util::where( self::$columnsDefaultCache, fn( string $column ) => $column !== reset( self::$columnsDefaultCache ))
    );
  }

  private static function setOrderByColumns(
    array $attributesCache
  ): array {
    return array_merge(
      Util::gets( $attributesCache, self::getColumnsDefault()->initials ),
      Util::notGets( $attributesCache, Util::merge(
        self::getColumnsDefault()->initials, self::getColumnsDefault()->ends 
      )), Util::gets( $attributesCache, self::getColumnsDefault()->ends )
    );
  }

  public static function getAttributes(
  ): Collection {
    if( isset( self::$attributesCache[ static::class ] ) ){
      return new Collection( 
        self::$attributesCache[ static::class ]
      );
    }

    self::$attributesCache = [];
    $reflectionClass = new ReflectionClass(
      static::class
    );

    foreach( $reflectionClass->getProperties( ReflectionProperty::IS_PUBLIC ) as $property ){
      foreach( $property->getAttributes() as $attribute ){
        $instance = $attribute->newInstance();

        if( Util::inArray( $instance->attributeType, [ AttributeType::oneToMany, AttributeType::oneToOne ]) === false ){
          self::$attributesCache[ static::class ][ $property->getName() ][
            $instance->attributeType->name
          ] = $instance;  
        }      
      }
    }

    self::$attributesCache[ static::class ] = Util::mapper(
      self::$attributesCache[ static::class ], 
      fn( array $attributes ) => new Collection($attributes) 
    );

    self::$attributesCache[ static::class ] = self::setOrderByColumns(
      self::$attributesCache[ static::class ]
    );
    
    return new Collection( self::$attributesCache[ static::class ]);
  }

  public static function getColumns(
  ): Collection {
    return self::getAttributes()->mapper(
      fn( Collection $column ) => new IColumn(
        $column, new IEntity( static::class )
      )
    );
  }

  public static function getColumnsForeigns(
  ): Collection {
    return self::getAttributes()->where(
      fn( Collection $columns ) => $columns->where(
        fn( IAbstractColumn $item ) => $item->attributeType === AttributeType::foreigns
      )->exist()
    );
  }
}