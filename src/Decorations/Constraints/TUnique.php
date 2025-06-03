<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class TUnique extends TAbstractColumn
{
  public TAttributeType $attributeType = TAttributeType::Uniques;

  public function __construct(
    public readonly int $uniqueGroup = 1
  ){}
}