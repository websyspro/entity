<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnType;

/**
 * Abstract base class for all column attribute definitions.
 * Provides common structure for column types, constraints, and SQL generation.
 * Extended by specific column type classes (Text, Number, Date, etc.) and constraint classes.
 */
class AbstractColumn
{
  public AttributeType $attributeType = AttributeType::column;
  public ColumnType $columnType = ColumnType::text;

  /**
   * Generates SQL definition for this column or constraint.
   * Must be overridden by child classes to provide specific SQL syntax.
   * 
   * @return string SQL definition string for schema generation
   */
  public function sql(
  ): string {
    /* Default implementation returns empty string, child classes should override */
    return "";
  }
}