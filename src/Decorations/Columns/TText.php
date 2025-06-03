<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use Websyspro\Entity\Enums\TColumnType;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class TText extends TAbstractColumn
{
  public TAttributeType $attributeType = TAttributeType::Column;
  public TColumnType $columnType = TColumnType::Text;

  public function __construct(
    public readonly int $size = 255
  ){}

  public function sql(
  ): string {
    return sprintf("varchar(%s)", ...[
      $this->size
    ]);
  } 
}