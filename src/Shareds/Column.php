<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnVirtual;
use Websyspro\Commons\Collection;

/**
 * Represents a database column definition extracted from entity property metadata.
 * Processes column attributes, determines if column is virtual, and manages entity association.
 * Distinguishes between physical database columns and virtual/computed properties.
 */
class Column
{
  public AbstractColumn|null $property = null;
  public ColumnVirtual|null $virtual = ColumnVirtual::Not;
  public Entity|null $entity = null;

  /**
   * Initializes column metadata by processing property attributes.
   * Determines column type, virtual status, and entity relationship.
   * 
   * @param string $class Fully qualified class name of the entity containing this column
   * @param string $name Property name representing the column
   * @param Collection $attributes Collection of PHP attributes attached to the property
   * @param AbstractColumn|null $property Extracted column attribute definition
   * @param ColumnVirtual|null $virtual Flag indicating if column is virtual (not persisted)
   * @param Entity|null $entity Entity metadata object for the owning class
   */
  public function __construct(
    public string $class,
    public string $name,
    public Collection $attributes
  ){
    /* Extract column attribute from property attributes */
    $this->defineAttribs();
    /* Determine if column is virtual or physical database column */
    $this->defineVirtual();
    /* Create entity metadata for owning class */
    $this->defineEntity();
    /* Clean up temporary data structures */
    $this->defineClears();
  }

  /**
   * Extracts column attribute from property's attribute collection.
   * Filters to find column-type attributes and assigns to property field.
   * 
   * @return void Sets $this->property with column attribute or null
   */
  private function defineAttribs(
  ): void {
    /* Filter attributes to find only column type definitions */
    $attributes = $this->attributes->where( 
      fn( AbstractColumn $column ) => (
        $column->attributeType === AttributeType::column
      )
    );
    
    /* Assign first column attribute if found, otherwise null */
    $this->property = $attributes->exist() 
      ? $attributes->first() : null;
  }

  /**
   * Determines if column is virtual (computed/transient) or physical (persisted).
   * Virtual columns are not mapped to database and used for computed values.
   * 
   * @return void Sets $this->virtual flag based on property existence
   */
  private function defineVirtual(
  ): void {
    /* Mark as virtual if no column attribute was found */
    if( $this->property === null ){
      $this->virtual = ColumnVirtual::Yes;
    }
  }

  /**
   * Creates entity metadata object for the class containing this column.
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