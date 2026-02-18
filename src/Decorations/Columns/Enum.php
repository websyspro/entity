<?php

namespace Websyspro\Entity\Decorations\Columns;

use Websyspro\Entity\Shareds\AbstractColumn;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Commons\Collection;
use Attribute;
use UnitEnum;

/**
 * PHP attribute for defining enum column type from PHP enum classes.
 * Generates ENUM SQL column with values extracted from PHP enum cases.
 * Automatically maps PHP enum values to database enum constraints.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class Enum
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::column;
  public ColumnType $columnType = ColumnType::longtext;

  /**
   * Initializes enum column attribute with PHP enum class reference.
   * 
   * @param string $enum Fully qualified PHP enum class name
   */
  public function __construct(
    public string $enum
  ){}

  /**
   * Generates SQL column definition for ENUM type with all enum values.
   * 
   * @return string SQL definition like "enum('value1','value2','value3')"
   */
  public function sql(
  ): string {
    /* Extract all enum cases and map to quoted string values */
    $enums = new Collection(
      $this->enum::cases()
    )->mapper(fn(UnitEnum $case) => "'{$case->value}'");

    /* Format ENUM SQL with comma-separated values */
    return sprintf("enum(%s)", ...[
      $enums->joinWithComma()
    ]);
  } 
}