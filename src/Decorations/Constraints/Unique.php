<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Unique extends IAbstractColumn
{
  public AttributeType $attributeType = AttributeType::uniques;

  public function __construct(
    public readonly int $uniqueGroup = 1
  ){}
}