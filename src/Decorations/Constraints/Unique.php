<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Unique extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::uniques;

  public function __construct(
    public readonly int $uniqueGroup = 1
  ){}
}