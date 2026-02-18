<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for marking property as primary key in entity.
 * Identifies the unique identifier column(s) for entity instances.
 * Supports both single-column and composite primary keys.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class PrimaryKey
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::primaryKey;
}