<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for defining boolean/flag column type in entity properties.
 * Generates SMALLINT SQL column for storing boolean values as 0 (false) or 1 (true).
 * Provides efficient storage for yes/no, active/inactive, enabled/disabled flags.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class Flag 
extends AbstractColumn
{
  public static AttributeType $attributeType = AttributeType::column;
  public static ColumnType $columnType = ColumnType::flag;

  /**
   * Generates SQL column definition for SMALLINT type used as boolean flag.
   * 
   * @return string SQL definition "smallint"
   */
  public function sql(
  ): string {
    /* Return SMALLINT SQL type for boolean flag storage */
    return "smallint";
  }
}