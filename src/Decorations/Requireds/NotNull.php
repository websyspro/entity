<?php

namespace Websyspro\Entity\Decorations\Requireds;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class NotNull
extends IAbstractColumn
{
  public AttributeType $attributeType = AttributeType::Requireds;
}