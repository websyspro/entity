<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class OneToOne
extends IAbstractColumn
{
  public AttributeType $attributeType = AttributeType::oneToOne;

  public function __construct(
    public readonly string $referenceClass
  ){}
}