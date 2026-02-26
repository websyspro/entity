<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;
use Websyspro\Entity\Shareds\ReferenceClass;

/**
 * PHP attribute for defining foreign key relationships between entities.
 * Establishes referential integrity by linking property to another entity's primary key.
 * Supports both string class names and ReferenceClass objects for target entity specification.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class ForeignKey
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::foreigns;

  /**
   * Initializes foreign key constraint with reference to target entity.
   * 
   * @param string $entityReference Target entity class or ReferenceClass object
   */
  public function __construct(
    public string $entityReference
  ){}
}