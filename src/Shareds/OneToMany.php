<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Commons\Collection;

/**
 * Represents one-to-many relationship definition from entity property metadata.
 * Processes ORM relationship attributes for parent-child entity associations.
 * Enables lazy loading and eager loading of related entity collections.
 */
class OneToMany
{
  public Entity|null $entity = null;
  public AbstractColumn|array|null $property = null;

  /**
   * Initializes one-to-many relationship metadata by processing property attributes.
   * Determines if property defines a one-to-many relationship and manages entity association.
   * 
   * @param string $class Fully qualified class name of the parent entity
   * @param string $name Property name that holds the collection of child entities
   * @param Collection $attributes Collection of PHP attributes attached to the property
   * @param Entity|null $entity Entity metadata object for the parent class
   * @param AbstractColumn|array|null $property Empty array if relationship exists, null otherwise
   */
  public function __construct(
    public string $class,
    public string $name,
    public Collection $attributes
  ){
    /* Extract one-to-many relationship attribute from property attributes */
    $this->defineAttribs();
    /* Create entity metadata for parent class */
    $this->defineEntity();
    /* Clean up temporary data structures */
    $this->defineClears();
  }

  /**
   * Extracts one-to-many relationship attribute from property's attribute collection.
   * Sets property to empty array if relationship exists, null otherwise.
   * 
   * @return void Sets $this->property with empty array or null
   */
  private function defineAttribs(
  ): void {
    /* Filter attributes to find only one-to-many relationship type definitions */
    $attributes = $this->attributes->where( 
      fn( AbstractColumn $column ) => (
        $column->attributeType === AttributeType::oneToMany
      )
    );
    
    /* Set to empty array if relationship exists, null if not found */
    $this->property = $attributes->exist() 
      ? [] : null;
  }  

  /**
   * Creates entity metadata object for the parent class in this relationship.
   * 
   * @return void Initializes $this->entity with Entity object
   */
  private function defineEntity(
  ): void {
    /* Instantiate Entity object with parent class name */
    $this->entity = new Entity( 
      $this->class
    );
  }

  /**
   * Releases temporary data to optimize memory usage after processing.
   * 
   * @return void Unsets attributes collection and class name
   */
  private function defineClears(
  ): void {
    /* Remove temporary data no longer needed after initialization */
    unset( 
      $this->attributes,
      $this->class
    );
  }  
}