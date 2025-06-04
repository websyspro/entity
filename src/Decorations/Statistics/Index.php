<?php

namespace Websyspro\Entity\Decorations\Statistics;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Index extends IAbstractColumn
{
  public AttributeType $attributeType = AttributeType::Indexes;

  public function __construct(
    public readonly int $indexGroup = 1
  ){}
}