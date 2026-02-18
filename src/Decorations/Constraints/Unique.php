<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for defining unique constraint on entity property.
 * Enforces data uniqueness at database level to prevent duplicate values.
 * Supports composite unique constraints via uniqueGroup parameter.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class Unique extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::uniques;

  /**
   * Initializes unique constraint with optional group identifier for composite constraints.
   * 
   * @param int $uniqueGroup Group ID for composite unique constraints (default: 1)
   */
  public function __construct(
    public readonly int $uniqueGroup = 1
  ){}
}