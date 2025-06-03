<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\TColumnType;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class TDecimal extends TAbstractColumn
{
  public TAttributeType $attributeType = TAttributeType::Column;
  public TColumnType $columnType = TColumnType::Decimal;

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