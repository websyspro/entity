<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Unique
{
  public AttributeType $attributeType = AttributeType::Uniques;

  public function __construct(
    public readonly int $uniqueGroup = 1
  ){}
}