<?php

namespace Websyspro\Entity\Decorations\Generations;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;

/**
 * PHP attribute for marking property as auto-increment in entity.
 * Enables automatic sequential number generation for primary key columns.
 * Typically used with integer primary keys for automatic ID assignment.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class AutoIncrement
{
  public AttributeType $attributeType = AttributeType::generations;
}