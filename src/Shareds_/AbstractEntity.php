<?php

namespace Websyspro\Entity\Shareds_;

use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\Generations\AutoIncrement;
use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Decorations\Statistics\Index;
use Websyspro\Entity\Decorations\BaseEntity;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Decorations\ColumnName;
use Websyspro\Entity\Interfaces\Entity;
use Websyspro\Entity\Consts\Patterns;
use Websyspro\Entity\Enums\MetaType;
use ReflectionAttribute;
use ReflectionProperty;
use ReflectionClass;

class AbstractEntity
{
  /** @var ReflectionClass */
  protected static ReflectionClass $cacheBaseReflectionClass;

  /** @var ReflectionClass[] */
  protected static array $cacheEntityReflectionClass = [];

  /** @var Entity[] */
  protected static array $cacheEntity = [];

  /** @var [] */
  protected static array $cacheEntityColumns = [];

  /** @var Property[] */
  protected static array $cacheEntityProperties = [];

  /** @var Property[] */
  protected static array $cacheEntityAttributes = [];
  
  /** @var EntityStructure[][] */
  protected static array $cacheEntityStructures = [];

  public static function meta(
    MetaType $metaType
  ): EntityStructure {
    self::createCacheReflectionClass();
    self::createCacheProperties();
    self::createCacheEntity();
    self::createCacheMetaTypes( $metaType );

    return self::$cacheEntityStructures[ static::class ][ $metaType->value ];
  }

  private static function createCacheEntity(
  ): void {
    if( isset( self::$cacheEntity[ static::class ]) === false){
      $entityPaths = preg_split( 
        Patterns::PATTERN_NAMESPACE_BREAKS, 
        static::class
      );

      $table = preg_replace( 
        Patterns::PATTERN_REMOVE_ENTITY_SUFIXO, "", end( 
          $entityPaths
        )
      );

      $attributes = self::$cacheEntityReflectionClass[ static::class ]
        ->getAttributes( EntityName::class );

      if( empty( $attributes ) === false ){
        foreach( $attributes as $attribute ){
          if( $attribute instanceof ReflectionAttribute ){
            $entityName = $attribute->newInstance();

            if( $entityName instanceof EntityName ){
              self::$cacheEntity[ static::class ] = new Entity( 
                static::class, $table, $entityName->name
              );
            }
          }
        }
      } else {
        self::$cacheEntity[ static::class ] = new Entity( static::class, $table, $table );
      }
    }
  }

  private static function createCacheProperties(
  ): void {
    if( isset( self::$cacheEntityProperties[ static::class ]) === false ){
      $properties = self::$cacheEntityReflectionClass[ static::class ]->getProperties( 
        ReflectionProperty::IS_PUBLIC
      );

      if( !empty( $properties )){
        foreach( $properties as $property ){
          $attributes = $property->getAttributes();

          if( !empty( $attributes )){
            foreach( $attributes as $attribute ){
              self::$cacheEntityProperties[ static::class ][] = new Property( 
                $property, $attribute 
              );
            }
          }
        }
      }
    }
  }

  private static function propertiesToColumns(
    array $propertiesStart = [],
    array $propertiesCenter = [],
    array $propertiesEnd = []
  ): array {
    if( isset( self::$cacheEntityColumns[ static::class ] ) === false ){
      $propertiesFromBase = isset( self::$cacheBaseReflectionClass ) && self::$cacheBaseReflectionClass instanceof ReflectionClass 
        ? self::$cacheBaseReflectionClass->getProperties( ReflectionProperty::IS_PUBLIC ) : [];

      $propertiesFromEntity = self::$cacheEntityReflectionClass[ static::class ]->getProperties( ReflectionProperty::IS_PUBLIC );

      foreach( $propertiesFromBase as $property ){
        if( $property instanceof ReflectionProperty ){
          if( $property === reset( $propertiesFromBase )){
            $propertiesStart[] = $property->name;
          } else $propertiesEnd[] = $property->name;
        }
      }

      foreach( $propertiesFromEntity as $property ){
        if( $property instanceof ReflectionProperty ){
          if( in_array( $property->name, array_merge( $propertiesStart, $propertiesEnd )) === false ){
            if( sizeof( $property->getAttributes()) !== 0 ){
              $propertiesCenter[] = $property->name;
            }
          }
        }
      }

      self::$cacheEntityColumns[ static::class ] = array_merge(
        $propertiesStart, $propertiesCenter, $propertiesEnd
      );
    }

    return self::$cacheEntityColumns[ static::class ];
  }

  private static function propertiesByAttribute(
    string $attribute,
    bool $isNewInstance = false
  ): array {
    if( isset( self::$cacheEntityAttributes[ static::class ][ $attribute ]) === false ){
      self::$cacheEntityAttributes[ static::class ][ $attribute ] = [];
      
      foreach( self::$cacheEntityProperties[ static::class ] as $property ){
        if( $property instanceof Property ){
          if( is_subclass_of( $property->attribute->getName(), $attribute )){
            self::$cacheEntityAttributes[ static::class ][ $attribute ][ 
              $property->property->name 
            ] = $isNewInstance ? $property->attribute->newInstance() : $property->attribute;
          } else {
            if( $property->attribute->getName() === $attribute ){
                self::$cacheEntityAttributes[ static::class ][ $attribute ][ 
                  $property->property->name 
                ] = $isNewInstance ? $property->attribute->newInstance() : $property->attribute;
              }
          }
        }   
      }
    }

    return self::$cacheEntityAttributes[ static::class ][ $attribute ];
  }

  private static function createCacheMetaTypes(
    MetaType $metaType
  ): void {
    if( $metaType === MetaType::Query ){
      if( isset( self::$cacheEntityStructures[ static::class ][ $metaType->value ]) === false ){
          self::$cacheEntityStructures[ static::class ][ $metaType->value ] = new EntityStructure(
          self::$cacheEntity[ static::class ], 
          self::propertiesToColumns(), 
          self::propertiesByAttribute( AbstractColumn::class, true ),
          self::propertiesByAttribute( ColumnName::class, true ),
          [], 
          [], 
          self::propertiesByAttribute( ForeignKey::class, true ), 
          self::propertiesByAttribute( PrimaryKey::class ),
          [],
          self::propertiesByAttribute( AutoIncrement::class )
        );
      }
    } else {
      if( isset( self::$cacheEntityStructures[ static::class ][ $metaType->value ]) === false ){
        self::$cacheEntityStructures[ static::class ][ $metaType->value ] = new EntityStructure(
          self::$cacheEntity[ static::class ], 
          self::propertiesToColumns(), 
          self::propertiesByAttribute( AbstractColumn::class, true ),
          self::propertiesByAttribute( ColumnName::class, true ),
          self::propertiesByAttribute( Index::class, true ),
          self::propertiesByAttribute( Unique::class, true ), 
          self::propertiesByAttribute( ForeignKey::class, true ), 
          self::propertiesByAttribute( PrimaryKey::class ),
          self::propertiesByAttribute( NotNull::class ),
          self::propertiesByAttribute( AutoIncrement::class )
        );
      }
    }
  }

  /**
   * Inicializa e armazena em cache as instâncias de ReflectionClass
   * utilizadas pela entidade atual.
   *
   * Esse método evita a recriação repetida de objetos ReflectionClass,
   * reduzindo custo de reflexão durante o parsing, hidratação e geração
   * de SQL da ORM.
   *
   * @return void
   */  
  private static function createCacheReflectionClass(
  ): void {
    if( isset( self::$cacheBaseReflectionClass ) === false ){
      if( is_subclass_of( static::class, BaseEntity::class ) === true ){
        self::$cacheBaseReflectionClass = new ReflectionClass( BaseEntity::class );
      }
    }

    if( isset( self::$cacheEntityReflectionClass[ static::class ]) === false ){
      self::$cacheEntityReflectionClass[ static::class ] = new ReflectionClass( static::class );
    }
  }

  public function include(
    callable|null $fn = null
  ): AbstractEntity {
    return $this;
  }
}