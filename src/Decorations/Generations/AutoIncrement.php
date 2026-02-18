<?php

namespace Websyspro\Entity\Decorations\Generations;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for marking property as auto-increment in entity.
 * Enables automatic sequential number generation for primary key columns.
 * Typically used with integer primary keys for automatic ID assignment.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class AutoIncrement
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::generations;
}