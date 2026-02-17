<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnVirtual;
use Websyspro\Commons\Collection;

/**
 * Represents a database index definition extracted from entity property metadata.
 * Processes index attributes for query optimization and performance tuning.
 * Supports both single-column and composite indexes.
 */
class Indexes
{
  public Entity|null $entity = null;
  public AbstractColumn|null $property = null;
  public ColumnVirtual|null $virtual = ColumnVirtual::Not;

  /**
   * Initializes index metadata by processing property attributes.
   * Determines if property has index definition and manages entity association.
   * 
   * @param string $class Fully qualified class name of the entity containing this index
   * @param string $name Property name that should be indexed
   * @param Collection $attributes Collection of PHP attributes attached to the property
   * @param Entity|null $entity Entity metadata object for the owning class
   * @param AbstractColumn|null $property Extracted index attribute definition
   * @param ColumnVirtual|null $virtual Flag indicating if index is virtual (not applied)
   */
  public function __construct(
    public string $class,
    public string $name,
    public Collection $attributes
  ){
    /* Extract index attribute from property attributes */
    $this->defineAttribs();
    /* Determine if index definition is valid or virtual */
    $this->defineVirtual();
    /* Create entity metadata for owning class */
    $this->defineEntity();
    /* Clean up temporary data structures */
    $this->defineClears();
  }
  
  /**
   * Extracts index attribute from property's attribute collection.
   * Filters to find index-type attributes for database optimization.
   * 
   * @return void Sets $this->property with index attribute or null
   */
  private function defineAttribs(
  ): void {
    /* Filter attributes to find only index type definitions */
    $attributes = $this->attributes->where( 
      fn( AbstractColumn $column ) => (
        $column->attributeType === AttributeType::indexes
      ) 
    );
    
    /* Assign first index attribute if found, otherwise null */
    $this->property = $attributes->exist() 
      ? $attributes->first() : null;
  }

  /**
   * Determines if index is virtual (not applied) or physical (created in database).
   * Virtual indexes are ignored during schema generation.
   * 
   * @return void Sets $this->virtual flag based on property existence
   */
  private function defineVirtual(
  ): void {
    /* Mark as virtual if no index attribute was found */
    if( $this->property === null ){
      $this->virtual = ColumnVirtual::Yes;
    }
  }

  /**
   * Creates entity metadata object for the class containing this index.
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