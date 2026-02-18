<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for defining time column type in entity properties.
 * Generates TIME SQL column for storing time values without date component.
 * Stores time in HH:MM:SS format in database.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class Time extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::column;
  public ColumnType $columnType = ColumnType::time;

  /**
   * Generates SQL column definition for TIME type.
   * 
   * @return string SQL definition "time"
   */
  public function sql(
  ): string {
    /* Return TIME SQL type for time-only storage */
    return "time";
  }
}