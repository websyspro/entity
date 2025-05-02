<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Index
{
  public AttributeType $attributeType = AttributeType::Indexes;

  public function __construct(
    public readonly int $indexGroup = 1
  ){}
}