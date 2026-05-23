<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for defining date column type in entity properties.
 * Generates DATE SQL column for storing date values without time component.
 * Stores dates in YYYY-MM-DD format in database.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class Date
extends AbstractColumn
{
  public static AttributeType $attributeType = AttributeType::column;
  public static ColumnType $columnType = ColumnType::date;

  /**
   * Generates SQL column definition for DATE type.
   * 
   * @return string SQL definition "date"
   */
  public function sql(
  ): string {
    /* Return DATE SQL type for date-only storage */
    return "date";
  }
}