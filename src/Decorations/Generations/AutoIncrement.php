<?php

namespace Websyspro\Entity\Decorations\Generations;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class AutoIncrement
extends IAbstractColumn
{
  public AttributeType $attributeType = AttributeType::generations;
}