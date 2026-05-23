<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for defining long text column type in entity properties.
 * Generates LONGTEXT SQL column for storing large text content without size limit.
 * Suitable for descriptions, content, JSON data, and other large text fields.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class LongText
extends AbstractColumn
{
  public static AttributeType $attributeType = AttributeType::column;
  public static ColumnType $columnType = ColumnType::longtext;

  /**
   * Initializes longtext column attribute (size parameter unused but kept for compatibility).
   * 
   * @param int $size Unused parameter, kept for interface compatibility
   */
  public function __construct(
    public readonly int $size = 255
  ){}

  /**
   * Generates SQL column definition for LONGTEXT type.
   * 
   * @return string SQL definition "longtext"
   */
  public function sql(
  ): string {
    /* Return LONGTEXT SQL type for large text storage */
    return sprintf("longtext");
  } 
}