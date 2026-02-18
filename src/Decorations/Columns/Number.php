<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for defining numeric/integer column type in entity properties.
 * Generates BIGINT SQL column for storing large integer values.
 * Suitable for IDs, counters, and numeric foreign keys.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class Number
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::column;
  public ColumnType $columnType = ColumnType::number;

  /**
   * Generates SQL column definition for BIGINT type.
   * 
   * @return string SQL definition "bigint"
   */
  public function sql(
  ): string {
    /* Return BIGINT SQL type for large integer storage */
    return "bigint";
  } 
}