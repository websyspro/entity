<?php

namespace Websyspro\Entity\Core\Bases;

use Websyspro\Entity\Shareds\EntityMeta;
use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Entity\Shareds\EntityColumns;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\Column;
use Websyspro\Entity\Shareds\Entity;
use Websyspro\Commons\Collection;
use ReflectionProperty;
use ReflectionClass;

class AbstractEntity
{
  protected static array $cacheMeta = [];
  protected static array $cacheCompiled = [];
  protected static array $cacheEntityStructure = [];
  protected static array $cacheQueryStructure = []; // 🔥 NOVO
  protected static array $cacheColumns = [];

  private static function getEntityColumns(): EntityColumns
  {
    if (empty(self::$cacheColumns)) {
      $reflectionClass = new ReflectionClass(BaseEntity::class);

      self::$cacheColumns = array_map(
        fn(ReflectionProperty $p) => $p->getName(),
        $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC)
      );
    }

    $first = self::$cacheColumns[0] ?? null;

    return new EntityColumns(
      [$first],
      array_slice(self::$cacheColumns, 1),
      self::$cacheColumns
    );
  }

  private static function compileMetadata(): void
  {
    if (isset(self::$cacheCompiled[static::class])) {
      return;
    }

    $reflectionClass = new ReflectionClass(static::class);

    $compiled = [
      'column'     => [],
      'indexes'    => [],
      'uniques'    => [],
      'foreigns'   => [],
      'primaryKey' => [],
      'requireds'  => [],
      'oneToMany'  => [],
      'oneToOne'   => [],
    ];

    foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
      $name = $property->getName();

      foreach ($property->getAttributes() as $attribute) {
        $instance = $attribute->newInstance();
        $type = $instance->attributeType->value;

        $compiled[$type][$name] = new Column($name, $instance);
      }
    }

    self::$cacheCompiled[static::class] = $compiled;
  }

  private static function getByType(AttributeType $type): Collection
  {
    self::compileMetadata();

    return new Collection(
      self::$cacheCompiled[static::class][$type->value] ?? []
    );
  }

  private static function getColumnsOptimized(): Collection
  {
    self::compileMetadata();

    $columns = self::$cacheCompiled[static::class]['column'] ?? [];

    return new Collection(
      array_map( fn(Column $c) => $c->name, $columns)
    );
  }

  // 🔥 FULL (SCHEMA)
  public static function getAttributes(): EntityStructure
  {
    if (!isset(self::$cacheEntityStructure[static::class])) {

      self::$cacheEntityStructure[static::class] = new EntityStructure(
        new Entity(static::class),
        self::getColumnsOptimized(),
        self::getByType(AttributeType::column),
        self::getByType(AttributeType::indexes),
        self::getByType(AttributeType::uniques),
        self::getByType(AttributeType::foreigns),
        self::getByType(AttributeType::primaryKey),
        self::getByType(AttributeType::requireds),
        self::getByType(AttributeType::oneToMany),
        self::getByType(AttributeType::oneToOne)
      );
    }

    return self::$cacheEntityStructure[static::class];
  }

  public static function getQueryAttributes(): EntityStructure {
    if( !isset(self::$cacheQueryStructure[ static::class ])){

      self::compileMetadata();

      self::$cacheQueryStructure[static::class] = new EntityStructure(
        new Entity(static::class),
        self::getColumnsOptimized(),
        self::getByType( AttributeType::column ),

        new Collection(),
        new Collection(),

        self::getByType(AttributeType::foreigns),
        self::getByType(AttributeType::primaryKey),

        new Collection(),
        new Collection(),
        new Collection()
      );
    }

    return self::$cacheQueryStructure[static::class];
  }

  public static function meta(): EntityMeta {
    if (!isset(self::$cacheMeta[static::class])) {
      self::$cacheMeta[static::class] = new EntityMeta(static::class);
    }

    return self::$cacheMeta[static::class];
  }  
}