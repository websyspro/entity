<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Enums\AttributeType;

/**
 * Processes and manages foreign key relationship metadata for entity properties.
 * Extracts foreign key attributes from entity properties and builds relationship structure.
 * Handles reference class resolution and entity mapping for ORM relationship management.
 */
class ForeignKeys
{
  public Entity|null $entity = null;
  public ReferenceClass|null $referenceClass = null;
  public AbstractColumn|array|null $property = null; 

  /**
   * Initializes foreign key metadata by processing property attributes.
   * Orchestrates extraction of foreign key definitions and relationship mapping.
   * 
   * @param string $class Fully qualified class name of the entity containing the foreign key
   * @param string $name Property name that holds the foreign key relationship
   * @param Collection $attributes Collection of PHP attributes attached to the property
   */
  public function __construct(
    public string $class,
    public string $name,
    public Collection $attributes
  ){
    /* Extract foreign key attribute from property attributes collection */
    $this->defineAttribs();
    /* Create entity metadata object for the owning class */
    $this->defineEntity();
    /* Resolve and map the referenced entity class */
    $this->defineRefers();
    /* Clean up temporary data to reduce memory footprint */
    $this->defineClears();
  }

  /**
   * Extracts foreign key attribute from property's attribute collection.
   * Filters attributes to find ForeignKey type and assigns to property field.
   * Sets property to null if no foreign key attribute is found.
   * 
   * @return void Modifies $this->property with extracted foreign key or null
   */
  private function defineAttribs(
  ): void {
    /* Filter attributes collection to find only foreign key type attributes */
    $attributes = $this->attributes->where( 
      fn( AbstractColumn $column ) => (
        $column->attributeType === AttributeType::foreigns
      ) 
    );
    
    /* Assign first foreign key attribute if exists, otherwise set to null */
    $this->property = $attributes->exist() 
      ? $attributes->first() : null;
  }  

  /**
   * Creates entity metadata object for the class containing this foreign key.
   * Provides access to entity-level information for relationship resolution.
   * 
   * @return void Initializes $this->entity with Entity object
   */
  private function defineEntity(
  ): void {
    /* Instantiate Entity object with the owning class name */
    $this->entity = new Entity( 
      $this->class
    );
  }

  /**
   * Resolves and maps the referenced entity class from foreign key definition.
   * Extracts target class information to establish relationship between entities.
   * Only processes if property contains valid ForeignKey attribute.
   * 
   * @return void Initializes $this->referenceClass with target entity metadata
   */
  private function defineRefers(
  ): void {
    /* Verify property is a valid ForeignKey instance before processing */
    if( $this->property instanceof ForeignKey ){
      /* Check if reference class is defined in the foreign key attribute */
      if( isset( $this->property->referenceClass )){
        /* Create ReferenceClass object to hold target entity metadata */
        $this->referenceClass = new ReferenceClass(
          $this->property->referenceClass
        );
      }
    }
  }

  /**
   * Releases temporary data structures to optimize memory usage.
   * Removes attributes collection and class name after processing is complete.
   * Keeps only essential relationship metadata for runtime operations.
   * 
   * @return void Unsets temporary properties
   */
  private function defineClears(
  ): void {
    /* Remove attributes collection and class name as they're no longer needed */
    unset( 
      $this->attributes,
      $this->class
    );
  }  
}