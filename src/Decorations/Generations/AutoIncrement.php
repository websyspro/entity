<?php

namespace Websyspro\Entity\Decorations\Generations;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class AutoIncrement extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::Generations;
}