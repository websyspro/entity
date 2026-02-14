<?php

namespace Websyspro\Entity\Core\Bases;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Websyspro\Commons\Collection;
use Websyspro\Entity\Enums\ColumnType;

class AbstractEntity
{
  protected static Collection|null $collumnsCache = null;

  public static function getColumns(
  ): Collection {
    if( self::$collumnsCache !== null){
      return self::$collumnsCache;
    }

    $reflectionClass = new ReflectionClass(
      static::class
    );

    $reflectionProperties = new Collection(
      $reflectionClass->getProperties(
        ReflectionProperty::IS_PUBLIC
      )
    );

    $columns = $reflectionProperties->mapper(
      function( ReflectionProperty $property ) {
        $attributes = new Collection(
          $property->getAttributes()
        );

        return $attributes->mapper(
          function( ReflectionAttribute $attribute ) use( $property ) {
            if( is_subclass_of( $attribute->getName(), ColumnType::class )) {
              $instance = $attribute->newInstance();
              return [
                "columnName" => $property->getName(),
                "columnType" => $instance->getColumnType(),
                "decoration" => $instance
              ];
            } else return [];
          }
        );
      }
    );

    return $columns;
  }
}