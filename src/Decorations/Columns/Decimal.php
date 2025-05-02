<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnType;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Decimal
{
  public AttributeType $attributeType = AttributeType::Column;
  public ColumnType $columnType = ColumnType::Date;

  public function __construct(
    public readonly int $numberOfDigits = 10,
    public readonly int $numberDigitsAfterTheComma = 2
  ){}

  public function sql(
  ): object {
    return (object)[
      "base" => $this->columnType,
      "type" => "decimal",
      "args" => "{$this->numberOfDigits},{$this->numberDigitsAfterTheComma}"
    ];
  }   
}