<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;

#[Attribute( Attribute::TARGET_PROPERTY )]
class ForeignKey
{
  public AttributeType $attributeType = AttributeType::Foreigns;

  public function __construct(
    public readonly string $referenceClass
  ){}
}