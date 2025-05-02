<?php

namespace Websyspro\Entity\Decorations\Statistics;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Index extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::Indexes;

  public function __construct(
    public readonly int $indexGroup = 1
  ){}
}