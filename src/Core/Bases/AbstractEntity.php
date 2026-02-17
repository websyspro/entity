<?php

namespace Websyspro\Entity\Core\Bases;

use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Entity\Shareds\UniquesGroups;
use Websyspro\Entity\Shareds\IndexesGroups;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\BaseColumns;
use Websyspro\Entity\Shareds\PrimaryKeys;
use Websyspro\Entity\Shareds\ForeignKeys;
use Websyspro\Entity\Shareds\Requireds;
use Websyspro\Entity\Shareds\OneToMany;
use Websyspro\Entity\Shareds\OneToOne;
use Websyspro\Entity\Shareds\Property;
use Websyspro\Entity\Shareds\Uniques;
use Websyspro\Entity\Shareds\Indexes;
use Websyspro\Entity\Shareds\Column;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionProperty;
use ReflectionClass;

class AbstractEntity
{
  protected static array $cacheAttrs = [];
  protected static array $cacheColumns = [];

  /**
   * Retrieves and caches base columns structure from BaseEntity class.
   * Implements lazy loading pattern to optimize performance by caching column definitions.
   * Separates columns into initial positions (e.g., primary keys) and end positions (e.g., timestamps).
   * 
   * @return BaseColumns Object containing arrays of initial and end column names for proper table structure ordering
   */
  private static function baseColumns(
  ): BaseColumns {
    /* Initialize cache on first access by loading BaseEntity column structure */
    if( Util::sizeArray( self::$cacheColumns ) === 0 ){
      /* Use reflection to dynamically inspect BaseEntity class structure at runtime */
      $reflectionClass = new ReflectionClass(
        BaseEntity::class
      );

      /* Map all public properties to their names and cache for subsequent calls */
      self::$cacheColumns = Util::mapper(
        $reflectionClass->getProperties( ReflectionProperty::IS_PUBLIC ),
        fn( ReflectionProperty $property ) => $property->name 
      );
    }

    /* Construct BaseColumns with first column as initial (primary key) and others as end columns (audit fields) */
    return new BaseColumns(
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
      )
    );
  }

  /**
   * Organizes columns into proper database table order following entity framework conventions.
   * Ensures base entity columns (ID, timestamps, soft delete) are positioned correctly.
   * Order: initial columns (PK) -> custom entity columns -> end columns (audit fields).
   * 
   * @param Collection $columns Unordered collection of Column objects from entity properties
   * @return Collection Properly ordered collection ready for database schema generation
   */
  private static function setColumnOrdem(
    Collection $columns
  ): Collection {
    /* Separate initial columns (primary key) that must appear first in table structure */
    $initials = $columns->where(
      fn( Column $column ) => Util::inArray( 
        $column->name, self::baseColumns()->initials 
      )
    );

    /* Isolate custom entity-specific columns that don't belong to base entity */
    $centers = $columns->where(
      fn( Column $column ) => (
        Util::inArray( $column->name, self::baseColumns()->initials ) === false && 
        Util::inArray( $column->name, self::baseColumns()->ends ) === false
      )
    );

    /* Separate end columns (created_at, updated_at, deleted_at) that must appear last */
    $ends = $columns->where(
      fn( Column $column ) => Util::inArray( 
        $column->name, self::baseColumns()->ends 
      )
    );

    /* Combine all groups in proper sequence to maintain consistent table structure across entities */
    return new Collection(
      array_merge(
        $initials->all(), 
        $centers->all(), 
        $ends->all()
      )
    );
  }

  /**
   * Extracts and processes database index or unique constraint metadata from entity properties.
   * Dynamically creates constraint objects by inspecting PHP attributes on entity properties.
   * Supports both single-column and composite indexes/unique constraints.
   * 
   * @param Collection $properties Collection of ReflectionProperty objects representing entity fields
   * @param AttributeType $attributeType Enum value specifying whether to extract indexes or unique constraints
   * @return Collection Validated collection of Indexes or Uniques objects ready for schema generation
   */
  private static function getAttrsIndexesOrUniques(
    Collection $properties,
    AttributeType $attributeType
  ): Collection {
    /* Convert each property to appropriate constraint object by reading PHP 8 attributes */
    $properties = $properties->mapper( 
      fn( Property $property ) => (
        AttributeType::indexes === $attributeType 
          ? new Indexes( 
            static::class,  
            $property->name, 
            $property->attributes
          ) 
          : new Uniques( 
            static::class,  
            $property->name, 
            $property->attributes
          )
      )
    );
      
    /* Remove invalid entries where attribute parsing failed or no constraint was defined */
    return $properties->where( 
      fn( Indexes|Uniques $index ) => (
        $index->property !== null 
      )
    );
  }

  /**
   * Filters out virtual properties that should not be persisted to database.
   * Virtual properties are used for computed values, relationships, or transient data.
   * Only properties with valid metadata are retained for database operations.
   * 
   * @param Collection $attributes Collection of attribute objects to validate
   * @return Collection Filtered collection excluding virtual/computed properties
   */
  private static function getDropVirtualYes(
    Collection $attributes
  ): Collection {
    /* Exclude properties marked as virtual or lacking proper database mapping metadata */
    return $attributes->where( 
      fn( mixed $attribute ) => (
        $attribute->property !== null
      )
    );
  }  

  /**
   * Sanitizes attribute collections by removing entries with null property references.
   * Ensures data integrity by filtering out attributes that failed validation or parsing.
   * Used as final cleanup step before caching attribute metadata.
   * 
   * @param Collection $attributes Collection of attribute objects that may contain invalid entries
   * @return Collection Clean collection with only valid, properly initialized attribute objects
   */
  private static function getDropPropertysNull(
    Collection $attributes
  ): Collection {
    /* Validate each attribute has successfully parsed property metadata before caching */
    return $attributes->where( 
      fn( mixed $attribute ) => (
        $attribute->property !== null
      )
    );
  }
  
  /**
   * Performs comprehensive entity metadata analysis and caches all database-related attributes.
   * Uses PHP reflection to inspect entity class structure and extract column definitions, constraints, and relationships.
   * Implements caching strategy to avoid repeated reflection overhead on subsequent calls.
   * Processes: columns, indexes, unique constraints, foreign keys, primary keys, required fields, and ORM relationships.
   * 
   * @return void Results are stored in static $cacheAttrs array indexed by entity class name
   */
  public static function getAttributes(
  ): EntityStructure {
    /* Initialize reflection to introspect current entity class structure at runtime */
    $reflectionClass = new ReflectionClass(
      static::class
    );

    /* Gather all public properties which represent database columns and relationship mappings */
    $properties = new Collection(
      Util::mapper(
        $reflectionClass->getProperties( 
          ReflectionProperty::IS_PUBLIC 
        ), fn( ReflectionProperty $property ) => new Property( $property ) 
      )
    );

    /* Process column definitions: map properties to Column objects, apply ordering, filter virtual columns */
    self::$cacheAttrs[ static::class ][ AttributeType::column->name ] = (
      self::getDropVirtualYes(
        self::setColumnOrdem(
          $properties->mapper( fn( Property $property ) => new Column(
            static::class, $property->name, $property->attributes
          ))
        )
      )
    );

    /* Extract and group index definitions for database performance optimization */
    self::$cacheAttrs[ static::class ][ AttributeType::indexes->name ] = new IndexesGroups(
      static::class, self::getAttrsIndexesOrUniques( 
        $properties, AttributeType::indexes
      )
    );

    /* Extract and group unique constraint definitions to enforce data integrity rules */
    self::$cacheAttrs[ static::class ][ AttributeType::uniques->name ] = new UniquesGroups(
      static::class, self::getAttrsIndexesOrUniques( 
        $properties, AttributeType::uniques
      )
    );

    /* Process foreign key relationships for referential integrity between tables */
    self::$cacheAttrs[ static::class ][ AttributeType::foreigns->name ] = self::getDropPropertysNull(
      $properties->mapper( fn( Property $property ) => new ForeignKeys(
        static::class, $property->name, $property->attributes
      ))
    );

    /* Identify primary key column(s) for entity identification and indexing */
    self::$cacheAttrs[ static::class ][ AttributeType::primaryKey->name ] = self::getDropPropertysNull(
      $properties->mapper( fn( Property $property ) => new PrimaryKeys(
        static::class, $property->name, $property->attributes
      ))
    );

    /* Mark required fields for validation and NOT NULL constraints in database schema */
    self::$cacheAttrs[ static::class ][ AttributeType::requireds->name ] = self::getDropPropertysNull(
      $properties->mapper( fn( Property $property ) => new Requireds(
        static::class, $property->name, $property->attributes
      ))
    );
    
    /* Map one-to-many relationships for ORM lazy loading and eager loading strategies */
    self::$cacheAttrs[ static::class ][ AttributeType::oneToMany->name ] = self::getDropPropertysNull(
      $properties->mapper( fn( Property $property ) => new OneToMany(
        static::class, $property->name, $property->attributes
      ))
    );
    
    /* Map one-to-one relationships for bidirectional entity associations */
    self::$cacheAttrs[ static::class ][ AttributeType::oneToOne->name ] = self::getDropPropertysNull(
      $properties->mapper( fn( Property $property ) => new OneToOne(
        static::class, $property->name, $property->attributes
      ))
    );

    return new EntityStructure(
      self::$cacheAttrs[ static::class ][ AttributeType::column->name ],
      self::$cacheAttrs[ static::class ][ AttributeType::indexes->name ],
      self::$cacheAttrs[ static::class ][ AttributeType::uniques->name ],
      self::$cacheAttrs[ static::class ][ AttributeType::foreigns->name ],
      self::$cacheAttrs[ static::class ][ AttributeType::primaryKey->name ],
      self::$cacheAttrs[ static::class ][ AttributeType::requireds->name ],
      self::$cacheAttrs[ static::class ][ AttributeType::oneToMany->name ],
      self::$cacheAttrs[ static::class ][ AttributeType::oneToOne->name ]
    );
  }
}