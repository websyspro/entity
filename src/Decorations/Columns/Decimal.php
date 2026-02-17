<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Decimal
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::column;
  public ColumnType $columnType = ColumnType::decimal;

  public function __construct(
    public readonly int $numberOfDigits = 10,
    public readonly int $numberDigitsAfterTheComma = 2
  ){}

  public function sql(
  ): string {
    return sprintf("decimal(%s,%s)", ...[
      $this->numberOfDigits,
      $this->numberDigitsAfterTheComma
    ]);
  }  
}