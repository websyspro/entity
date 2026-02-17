<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class OneToMany
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::oneToMany;

  public function __construct(
    public readonly string $referenceClass
  ){}
}