<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class TPrimaryKey extends TAbstractColumn
{
  public TAttributeType $attributeType = TAttributeType::PrimaryKey;
}