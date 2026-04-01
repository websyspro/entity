<?php

namespace Websyspro\Entity\Core\Bases;

use Websyspro\Commons\Collection;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Decorations\Statistics\Index;
use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\LongText;
use Websyspro\Entity\Decorations\Columns\Decimal;
use Websyspro\Entity\Decorations\Columns\Number;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Columns\Time;
use Websyspro\Entity\Decorations\Columns\Enum;
use Websyspro\Entity\Decorations\Columns\Date;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Entity\Shareds\AbstractColumn;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\Entity;
use Websyspro\Entity\Shareds\Column;
use Websyspro\Entity\Enums\MetaType;
use ReflectionAttribute;
use ReflectionProperty;
use ReflectionClass;

/**
 * Class AbstractEntity
 *
 * Core metadata engine responsible for extracting and caching
 * entity structure information using PHP Reflection and Attributes.
 *
 * This class analyzes entity classes and builds structured metadata
 * used for query generation and schema definition.
 *
 * Responsibilities:
 * - Extract public properties
 * - Identify column attributes
 * - Resolve constraints (PK, FK, Index, Unique, Required)
 * - Cache metadata for performance
 *
 * Acts as the foundation for ORM-like behavior.
 */
class AbstractEntity
{
  protected static ReflectionClass $cacheReflectionClassBase;
  protected static array $cacheReflectionClass = [];
  protected static array $cacheColumnsBase = [];
  protected static array $cacheColumns = [];
  protected static array $cacheAttributes = [];
  protected static array $cacheAttributesByType = [];
  protected static array $cacheForeignKeys = [];

  /**
   * Returns the first base column (usually primary identifier).
   *
   * @return array<string>
   */
  private static function getColumnsBaseStarts(
  ): array {
    return array_filter(
      self::$cacheColumnsBase,
      fn(string $column) => $column === reset(self::$cacheColumnsBase)
    );
  }

  /**
   * Returns non-base columns (custom entity fields).
   *
   * @return array<string>
   */
  private static function getColumsCenters(
  ): array {
    return array_filter(
      self::$cacheColumns[ static::class ][ AttributeType::column->name ],
      fn(string $column) => !in_array( $column, self::$cacheColumnsBase )
    );
  }

  /**
   * Returns remaining base columns excluding the first one.
   *
   * @return array<string>
   */
  private static function getColumnsBaseEnds(
  ): array {
    return array_filter(
      self::$cacheColumnsBase,
      fn(string $column) => $column !== reset( self::$cacheColumnsBase )
    );
  }

  /**
   * Retrieves all column names for the current entity.
   *
   * Combines:
   * - Base entity columns
   * - Custom entity columns (filtered by attributes)
     * Results are cached per class for performance.
   *
   * @return array<string>
   */
  private static function getColumns(
  ): array {
    if (empty(self::$cacheColumnsBase)) {
      self::$cacheColumnsBase = array_map(
        fn(ReflectionProperty $column) => $column->name,
          self::$cacheReflectionClassBase->getProperties(
            ReflectionProperty::IS_PUBLIC
          )
      );
    }

    if (empty(self::$cacheColumns[ static::class ][ AttributeType::column->name ])) {
      self::$cacheColumns[ static::class ][ AttributeType::column->name ] = array_map(
        fn(ReflectionProperty $column) => $column->name, array_filter(
          self::$cacheReflectionClass[static::class]->getProperties( ReflectionProperty::IS_PUBLIC ),
            fn(ReflectionProperty $column) => !empty($column->getAttributes(static::class))
        )
      );

      self::$cacheColumns[ static::class ][ AttributeType::column->name ] = array_merge(
        self::getColumnsBaseStarts(), self::getColumsCenters(), self::getColumnsBaseEnds()
      );
    }

    return self::$cacheColumns[ static::class ][ AttributeType::column->name ];
  }

  /**
   * Determines if an attribute type represents a constraint.
   *
   * @param string $columnType
   * @return bool
   */
  private static function isConstrants(
    string $columnType
  ): bool {
    return in_array($columnType, [
      ForeignKey::class,
      PrimaryKey::class,
      Index::class,
      Unique::class
    ]);
  }

  /**
   * Determines if an attribute type represents a column field.
   *
   * @param string $columnType
   * @return bool
   */
  private static function isColumnField(
    string $columnType
  ): bool {
    return in_array($columnType, [
      Date::class,
      Datetime::class,
      Decimal::class,
      Enum::class,
      Flag::class,
      LongText::class,
      Number::class,
      Text::class,
      Time::class
    ]);
  }

  /**
   * Extracts attributes from entity properties filtered by type.
   *
   * This method:
   * - Uses Reflection to inspect properties
   * - Caches attribute metadata
   * - Optionally instantiates attributes for constraints
   *
   * @param string $columnType
   * @return array<Column>
   */
  private static function getColumnByType(
    string $columnType
  ): array {
    if (empty(self::$cacheAttributes[static::class])) {
      $propertys = self::$cacheReflectionClass[static::class]
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

    if ( !isset( self::$cacheAttributesByType[ static::class ][ $columnType ])) {
      self::$cacheAttributesByType[ static::class ][ $columnType ] = [];

      if (self::isConstrants($columnType)) {
        foreach( self::$cacheAttributes[ static::class ] as $column ){
          if ( $column instanceof Column && $column->columnType === $columnType ) {
            if ($column->instance instanceof ReflectionAttribute) {
              $column->instance = $column->instance->newInstance();
            }

            self::$cacheAttributesByType[ static::class ][ $columnType ][] = $column;
          }
        }
      } else {
        foreach ( self::$cacheAttributes[ static::class ] as $column ){
          if ($column->instance instanceof ReflectionAttribute) {
            if (self::isColumnField( $column->columnType )) {
              self::$cacheAttributesByType[ static::class ][ $columnType ][] = $column;
            }
          }
        }
      }
    }

    return self::$cacheAttributesByType[ static::class ][ $columnType ];
  }

  /**
   * Returns column type metadata.
   *
   * @return array<Column>
   */
  private static function getTypes(
  ): array {
    return self::getColumnByType( AbstractColumn::class );
  }

  /**
   * Returns foreign key definitions.
   *
   * @return array<Column>
   */
  private static function getForeignKeys(
  ): array {
    return self::getColumnByType( ForeignKey::class );
  }

  /**
   * Returns primary key definitions.
   *
   * @return array<Column>
   */
  private static function getPrimaryKeys(
  ): array {
    return self::getColumnByType( PrimaryKey::class );
  }

  /**
   * Returns index definitions.
   *
   * @return array<Column>
   */
  private static function getIndexes(
  ): array {
    return self::getColumnByType( Index::class );
  }

  /**
   * Returns unique constraints.
   *
   * @return array<Column>
   */
  private static function getUnique(
  ): array {
    return self::getColumnByType( Unique::class );
  }

  /**
   * Returns required (NotNull) constraints.
   *
   * @return array<Column>
   */
  private static function getRequireds(
  ): array {
    return self::getColumnByType( NotNull::class );
  }

  /**
   * Builds the EntityStructure metadata based on the given MetaType.
   *
   * Meta types:
   * - Query: used for query building
   * - Schema: used for schema generation
   *
   * @param MetaType $metaType
   * @return EntityStructure
   */
  public static function meta(
    MetaType $metaType
  ): EntityStructure {
    if (!isset(self::$cacheReflectionClassBase)) {
      self::$cacheReflectionClassBase = new ReflectionClass( BaseEntity::class );
    }

    if (!isset(self::$cacheReflectionClass[ static::class ])) {
      self::$cacheReflectionClass[ static::class ] = new ReflectionClass( static::class );
    }

    if ($metaType === MetaType::Query) {
      return new EntityStructure(
        new Entity(static::class),
        self::getColumns(),
        self::getTypes(),
        [],
        [],
        self::getForeignKeys(),
        self::getPrimaryKeys(),
        []
      );
    } else if ($metaType === MetaType::Schema) {
      return new EntityStructure(
        new Entity(static::class),
        self::getColumns(),
        self::getTypes(),
        self::getIndexes(),
        self::getUnique(),
        self::getForeignKeys(),
        self::getPrimaryKeys(),
        self::getRequireds()
      );
    }

    return new EntityStructure(
      new Entity(static::class)
    );
  }
}