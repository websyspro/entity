<?php

namespace Websyspro\Entity\Core\Bases;

use ReflectionClass;
use ReflectionProperty;

class AbstractEntity
{
  protected static array|null $collumnsCache = null;

  public static function getColumns(
    array $columns = []
  ): array|null {
    if( self::$collumnsCache !== null){
      return self::$collumnsCache;
    }

    $reflectionClass = new ReflectionClass(
      static::class
    );

    $reflectionProperties = $reflectionClass->getProperties(
      ReflectionProperty::IS_PUBLIC
    );

    foreach( $reflectionProperties as $property ){
      $attributes = $property->getAttributes();
      $attributesToColumns = [];

      foreach( $attributes as $attribute ){
        $instance = $attribute->newInstance();

        $attributesToColumns[
          $instance->attributeType->name
        ] = (object)[
          "columnType" => $attribute->getName(),
          "instance" => $instance
        ];
      }

      $columns[$property->getName()] = $attributesToColumns;
    }

    return $columns;
  }
}