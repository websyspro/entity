<?php

namespace Websyspro\Entity\Decorations;

use Attribute;

/**
 * PHP attribute for defining list of entities in a module or package.
 * Applied at class level to register multiple entity classes for batch processing.
 * Used for migration generation, schema updates, and entity discovery.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class EntityList
{
  /**
   * Initializes entity list with array of entity class names.
   * 
   * @param array $entitys Array of fully qualified entity class names
   */
  public function __construct(
    public array $entitys = []
  ){}
}