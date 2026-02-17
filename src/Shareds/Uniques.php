<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnVirtual;
use Websyspro\Commons\Collection;

/**
 * Represents a unique constraint definition extracted from entity property metadata.
 * Processes unique constraint attributes to enforce data uniqueness at database level.
 * Supports both single-column and composite unique constraints.
 */
class Uniques
{
  public Entity|null $entity = null;
  public AbstractColumn|null $property = null;
  public ColumnVirtual|null $virtual = ColumnVirtual::Not;

  /**
   * Initializes unique constraint metadata by processing property attributes.
   * Determines if property has unique constraint and manages entity association.
   * 
   * @param string $class Fully qualified class name of the entity containing this unique constraint
   * @param string $name Property name that should have unique constraint
   * @param Collection $attributes Collection of PHP attributes attached to the property
   * @param Entity|null $entity Entity metadata object for the owning class
   * @param AbstractColumn|null $property Extracted unique constraint attribute definition
   * @param ColumnVirtual|null $virtual Flag indicating if constraint is virtual (not applied)
   */
  public function __construct(
    public string $class,
    public string $name,
    public Collection $attributes
  ){
    /* Extract unique constraint attribute from property attributes */
    $this->defineAttribs();
    /* Determine if unique constraint is valid or virtual */
    $this->defineVirtual();
    /* Create entity metadata for owning class */
    $this->defineEntity();
    /* Clean up temporary data structures */
    $this->defineClears();
  }
  
  /**
   * Extracts unique constraint attribute from property's attribute collection.
   * Filters to find unique-type attributes for data integrity enforcement.
   * 
   * @return void Sets $this->property with unique constraint attribute or null
   */
  private function defineAttribs(
  ): void {
    /* Filter attributes to find only unique constraint type definitions */
    $attributes = $this->attributes->where( 
      fn( AbstractColumn $column ) => (
        $column->attributeType === AttributeType::uniques
      ) 
    );
    
    /* Assign first unique constraint attribute if found, otherwise null */
    $this->property = $attributes->exist() 
      ? $attributes->first() : null;
  }

  /**
   * Determines if unique constraint is virtual (not applied) or physical (created in database).
   * Virtual constraints are ignored during schema generation.
   * 
   * @return void Sets $this->virtual flag based on property existence
   */
  private function defineVirtual(
  ): void {
    /* Mark as virtual if no unique constraint attribute was found */
    if( $this->property === null ){
      $this->virtual = ColumnVirtual::Yes;
    }
  }

  /**
   * Creates entity metadata object for the class containing this unique constraint.
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