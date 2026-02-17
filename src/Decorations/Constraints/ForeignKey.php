<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;
use Websyspro\Entity\Shareds\ReferenceClass;

#[Attribute( Attribute::TARGET_PROPERTY )]
class ForeignKey
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::foreigns;

  public function __construct(
    public ReferenceClass|string $referenceClass
  ){}
}