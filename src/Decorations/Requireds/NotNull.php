<?php

namespace Websyspro\Entity\Decorations\Requireds;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class NotNull extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::Requireds;
}