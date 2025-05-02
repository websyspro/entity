<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;

#[Attribute( Attribute::TARGET_PROPERTY )]
class PrimaryKey
{
  public AttributeType $attributeType = AttributeType::PrimaryKey;
}