<?php

namespace Websyspro\Entity\Decorations\Generations;

use Attribute;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class TAutoIncrement extends TAbstractColumn
{
  public TAttributeType $attributeType = TAttributeType::Generations;
}