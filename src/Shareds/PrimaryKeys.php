<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Commons\Collection;

/**
 * Represents primary key definition extracted from entity property metadata.
 * Processes primary key attributes for entity identification and indexing.
 * Supports both single-column and composite primary keys.
 */
class PrimaryKeys
{
  public Entity|null $entity = null;
  public AbstractColumn|array|null $property = null;

  /**
   * Initializes primary key metadata by processing property attributes.
   * Determines if property is part of primary key and manages entity association.
   * 
   * @param string $class Fully qualified class name of the entity containing this primary key
   * @param string $name Property name that is part of the primary key
   * @param Collection $attributes Collection of PHP attributes attached to the property
   * @param Entity|null $entity Entity metadata object for the owning class
   * @param AbstractColumn|array|null $property Extracted primary key attribute or empty array if valid
   */
  public function __construct(
    public string $class,
    public string $name,
    public Collection $attributes
  ){
    /* Extract primary key attribute from property attributes */
    $this->defineAttribs();
    /* Create entity metadata for owning class */
    $this->defineEntity();
    /* Clean up temporary data structures */
    $this->defineClears();
  }

  /**
   * Extracts primary key attribute from property's attribute collection.
   * Sets property to empty array if primary key exists, null otherwise.
   * Empty array indicates valid primary key for composite key support.
   * 
   * @return void Sets $this->property with empty array or null
   */
  private function defineAttribs(
  ): void {
    /* Filter attributes to find only primary key type definitions */
    $attributes = $this->attributes->where( 
      fn( AbstractColumn $column ) => (
        $column->attributeType === AttributeType::primaryKey
      ) 
    );
    
    /* Set to empty array if primary key exists, null if not found */
    $this->property = $attributes->exist() 
      ? [] : null;
  }  

  /**
   * Creates entity metadata object for the class containing this primary key.
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