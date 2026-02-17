<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Commons\Collection;

/**
 * Represents required field (NOT NULL) constraint definition from entity property metadata.
 * Processes required field attributes for validation and database schema generation.
 * Enforces NOT NULL constraints at database level.
 */
class Requireds
{
  public Entity|null $entity = null;
  public AbstractColumn|array|null $property = null;

  /**
   * Initializes required field metadata by processing property attributes.
   * Determines if property is required (NOT NULL) and manages entity association.
   * 
   * @param string $class Fully qualified class name of the entity containing this required field
   * @param string $name Property name that is required
   * @param Collection $attributes Collection of PHP attributes attached to the property
   * @param Entity|null $entity Entity metadata object for the owning class
   * @param AbstractColumn|array|null $property Empty array if required, null otherwise
   */
  public function __construct(
    public string $class,
    public string $name,
    public Collection $attributes
  ){
    /* Extract required field attribute from property attributes */
    $this->defineAttribs();
    /* Create entity metadata for owning class */
    $this->defineEntity();
    /* Clean up temporary data structures */
    $this->defineClears();
  }

  /**
   * Extracts required field attribute from property's attribute collection.
   * Sets property to empty array if required attribute exists, null otherwise.
   * 
   * @return void Sets $this->property with empty array or null
   */
  private function defineAttribs(
  ): void {
    /* Filter attributes to find only required field type definitions */
    $attributes = $this->attributes->where( 
      fn( AbstractColumn $column ) => (
        $column->attributeType === AttributeType::requireds
      )
    );
    
    /* Set to empty array if required attribute exists, null if not found */
    $this->property = $attributes->exist() 
      ? [] : null;
  }  

  /**
   * Creates entity metadata object for the class containing this required field.
   * 
   * @return void Initializes $this->entity with Entity object
   */
  private function defineEntity(
  ): void {
    /* Instantiate Entity object with owning class name */
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