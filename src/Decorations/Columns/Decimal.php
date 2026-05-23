<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for defining decimal/numeric column type with precision in entity properties.
 * Generates DECIMAL SQL column for storing fixed-point numbers with configurable precision.
 * Ideal for monetary values, percentages, and measurements requiring exact decimal representation.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class Decimal
extends AbstractColumn
{
  public static AttributeType $attributeType = AttributeType::column;
  public static ColumnType $columnType = ColumnType::decimal;

  /**
   * Initializes decimal column attribute with precision configuration.
   * 
   * @param int $numberOfDigits Total number of digits (default: 10)
   * @param int $numberDigitsAfterTheComma Number of decimal places (default: 2)
   */
  public function __construct(
    public readonly int $numberOfDigits = 10,
    public readonly int $numberDigitsAfterTheComma = 2
  ){}

  /**
   * Generates SQL column definition for DECIMAL type with precision.
   * 
   * @return string SQL definition like "decimal(10,2)"
   */
  public function sql(
  ): string {
    /* Format DECIMAL SQL with total digits and decimal places */
    return sprintf("decimal(%s,%s)", ...[
      $this->numberOfDigits,
      $this->numberDigitsAfterTheComma
    ]);
  }  
}