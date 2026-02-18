<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for defining one-to-one relationship in entity.
 * Represents bidirectional relationship where each entity has exactly one related entity.
 * Enables ORM to navigate between related entities in both directions.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class OneToOne
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::oneToOne;

  /**
   * Initializes one-to-one relationship with target entity class.
   * 
   * @param string $referenceClass Fully qualified class name of related entity
   */
  public function __construct(
    public readonly string $referenceClass
  ){}
}