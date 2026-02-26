<?php

namespace Websyspro\Entity\Decorations\Constraints;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

/**
 * PHP attribute for defining one-to-many relationship in entity.
 * Represents parent side of relationship where one entity has many related child entities.
 * Enables ORM to load collections of related entities via lazy or eager loading.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class OneToMany
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::oneToMany;

  /**
   * Initializes one-to-many relationship with target entity class.
   * 
   * @param string $entityReference Fully qualified class name of child entity
   */
  public function __construct(
    public string $entityReference
  ){}
}