<?php

namespace Websyspro\Entity;

use Closure;
use JsonSerializable;
use ReflectionClass;
use Websyspro\Entity\Types\ColumnList;

/**
 * Alias para BaseIncrementEntity — mantido para compatibilidade.
 * Use BaseIncrementEntity ou BaseUUIDEntity diretamente.
 */
abstract class BaseEntity
implements JsonSerializable
{
  private static array $orderCache = [];

  public function as(
    string $alias    
  ): void {}

  public function sum(
    mixed $expr
  ): void {}

  public function include(
    mixed ...$closure
  ): void {}    

  private static function resolveOrder(): array {
    $class = static::class;

    if( isset( static::$orderCache[ $class ] ) ){
      return static::$orderCache[ $class ];
    }

    $reflection = new ReflectionClass( $class );
    $self       = [];
    $base       = [];

    foreach( $reflection->getProperties() as $property ){
      $typeName = $property->getType()?->getName();

      if( $typeName === ColumnList::class ){
        continue;
      }

      $name = $property->getName();

      if( $reflection->getName() === $property->class ){
        $self[] = $name;
      } else {
        $base[] = $name;
      }
    }

    // ordem: id + props da classe filha + resto da base
    $id     = in_array( "id", $base, true ) ? [ "id" ] : [];
    $rest   = array_values( array_filter( $base, fn($p) => $p !== "id" ) );
    $order  = array_merge( $id, $self, $rest );

    static::$orderCache[ $class ] = $order;
    return $order;
  }

  public function jsonSerialize(): mixed {
    $order  = static::resolveOrder();
    $result = [];

    foreach( $order as $name ){
      if( isset( $this->{ $name } ) === false ){
        continue;
      }

      $value = $this->{ $name };

      if( is_object( $value ) && method_exists( $value, "getValue" ) ){
        $value = $value->getValue();
      }

      $result[ $name ] = $value;
    }

    // props fora da ordem (ex: includes/relacionamentos setados dinamicamente)
    foreach( get_object_vars( $this ) as $prop => $val ){
      if( in_array( $prop, $order, true ) === false ){
        $result[ $prop ] = $val;
      }
    }

    return $result;
  }
}
