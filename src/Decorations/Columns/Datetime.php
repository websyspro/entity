<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for defining datetime column type in entity properties.
 * Generates DATETIME SQL column for storing date and time values.
 * Stores timestamps in YYYY-MM-DD HH:MM:SS format in database.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class Datetime 
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::column;
  public ColumnType $columnType = ColumnType::datetime;

  /**
   * Generates SQL column definition for DATETIME type.
   * 
   * @return string SQL definition "datetime"
   */
  public function sql(
  ): string {
    /* Return DATETIME SQL type for timestamp storage */
    return "datetime";
  }
}