<?php

namespace Websyspro\Entity\Decorations;

use Attribute;
use Websyspro\Commons\Collection;

/**
 * PHP attribute for defining list of entities in a module or package.
 * Applied at class level to register multiple entity classes for batch processing.
 * Used for migration generation, schema updates, and entity discovery.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class EntityList
{
  /**
   * Initializes entity list with array of entity class names.
   * 
   * @param array $entity Array of fully qualified entity class names
   */
  public function __construct(
    public string $entity
  ){}

  public function where(
    callable|null $fn = null
  ): EntityList {
    return $this;
  }

  public function include(
    callable|null $fn = null
  ): EntityList {
    return $this;
  }

  public function any(
    callable|null $fn = null
  ): EntityList {
    return $this;
  }  

  public function none(
    callable|null $fn = null
  ): EntityList {
    return $this;
  }  
  
  public function sum(
    callable|null $fn = null
  ): EntityList {
    return $this;
  }  
}