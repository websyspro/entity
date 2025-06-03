<?php

namespace Websyspro\Entity\Decorations\Statistics;

use Attribute;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class TIndex extends TAbstractColumn
{
  public TAttributeType $attributeType = TAttributeType::Indexes;

  public function __construct(
    public readonly int $indexGroup = 1
  ){}
}