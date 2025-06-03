<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class TForeignKey extends TAbstractColumn
{
  public TAttributeType $attributeType = TAttributeType::Foreigns;

  public function __construct(
    public readonly string $referenceClass
  ){}
}