<?php

namespace Websyspro\Entity\Decorations\Requireds;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for marking property as required (NOT NULL) in entity.
 * Enforces NOT NULL constraint at database level to prevent null values.
 * Used for mandatory fields that must always have a value.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class NotNull
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::requireds;
}