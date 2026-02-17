<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class OneToOne
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::oneToOne;

  public function __construct(
    public readonly string $referenceClass
  ){}
}