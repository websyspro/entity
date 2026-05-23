<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for defining text/varchar column type in entity properties.
 * Generates VARCHAR SQL column with configurable size for string data storage.
 * Default size is 255 characters, can be customized via constructor parameter.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class Text
extends AbstractColumn
{
  public static AttributeType $attributeType = AttributeType::column;
  public static ColumnType $columnType = ColumnType::text;

  /**
   * Initializes text column attribute with specified size.
   * 
   * @param int $size Maximum character length for VARCHAR column (default: 255)
   */
  public function __construct(
    public readonly int $size = 255
  ){}

  /**
   * Generates SQL column definition for VARCHAR type.
   * 
   * @return string SQL definition like "varchar(255)"
   */
  public function sql(
  ): string {
    /* Format VARCHAR SQL with configured size */
    return sprintf("varchar(%s)", ...[
      $this->size
    ]);
  } 
}