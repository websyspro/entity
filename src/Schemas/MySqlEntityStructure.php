<?php

namespace Websyspro\Entity\Schemas;

use Websyspro\Entity\Decorators\Column;
use Websyspro\Entity\Decorators\ForeignKey;
use Websyspro\Entity\Decorators\Index;
use Websyspro\Entity\Decorators\InitialDefault;
use Websyspro\Entity\Decorators\Length;
use Websyspro\Entity\Decorators\Precision;
use Websyspro\Entity\Decorators\PrimaryKey;
use Websyspro\Entity\Decorators\Required;
use Websyspro\Entity\Decorators\Unique;
use Websyspro\Entity\Interfaces\ForeignKeyStructure;
use Websyspro\Entity\Interfaces\PrecisionDetails;
use Websyspro\Entity\Types\ColumnAutoIncrement;
use Websyspro\Entity\Types\ColumnAutoUUID;
use Websyspro\Entity\Types\ColumnUUID;
use ReflectionClass;
use function in_array;

class MySqlEntityStructure
extends AbstractEntityStructure
{
  public function defineTypes(
    string $name,
    string $type
  ): void {
    $this->types->items[ $name ] = $type;

    if( in_array( $type, [ ColumnUUID::class ])){
      $this->lengths->items[ $name ] = 36;
    }
  }

  public function defineGenerateds(
    string $name,
    string $type
  ): void {
    if( in_array( $type, [ ColumnAutoIncrement::class, ColumnAutoUUID::class ])){
      $this->generateds->items[ $name ] = $name;
    }
  }

  public function defineAlias(
    string $name,
    mixed $instance
  ): void {
    if( $instance instanceof Column ){
      $this->alias->items[ $name ] = $instance->name;
    }
  }
  
  public function defineLengths(
    string $name,
    mixed $instance
  ): void {
    if( $instance instanceof Length ){
      $this->lengths->items[ $name ] = $instance->value;
    }    
  } 
  
  public function definePrecisions(
    string $name,
    mixed $instance
  ): void {
    if( $instance instanceof Precision ){
      $this->precisions->items[ $name ] = new PrecisionDetails(
        $instance->precision, $instance->scale
      );
    }    
  }
  
  public function defineRequireds(
    string $name,
    mixed $instance
  ): void {
    if( $instance instanceof Required ){
      $this->requireds->items[ $name ] = $name;
    }
  }

  public function definePrimaryKeys(
    string $name,
    mixed $instance
  ): void {
    if( $instance instanceof PrimaryKey ){
      $this->primaryKeys->items[ $name ] = $name;
    }    
  }

  public function defineIndexes(
    string $name,
    mixed $instance
  ): void {
    if( $instance instanceof Index ){
      $this->indexes->items[ $instance->group ][] = $name;
    }
  }

  public function defineUniques(
    string $name,
    mixed $instance
  ): void {
    if( $instance instanceof Unique ){
      $this->uniques->items[ $instance->group ][] = $name;
    }
  }

  public function defineForeignKeys(
    string $name,
    mixed $instance
  ): void {
    if( $instance instanceof ForeignKey ){
      $entityInstance = new MySqlEntityStructure( new ReflectionClass( $instance->entity ));
      if( $entityInstance instanceof MySqlEntityStructure ){
        [ $generatedColumn ] = array_values( $entityInstance->generateds->items );
        
        $this->foreignKeys->items[ $name ] = new ForeignKeyStructure(
          $entityInstance->entityNames, $this->primaryKeys->items[ $generatedColumn ] ?? $generatedColumn
        );
      }
    }    
  }
  
  public function defineInitialDefaults(
    string $name,
    mixed $instance
  ): void {
    if( $instance instanceof InitialDefault ){
      $this->initialDefaults->items[ $name ] = $instance->generator;
    }    
  }  
}