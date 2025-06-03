<?php

namespace Websyspro\Entity\Decorations\Requireds;

use Attribute;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class TNotNull extends TAbstractColumn
{
  public TAttributeType $attributeType = TAttributeType::Requireds;
}