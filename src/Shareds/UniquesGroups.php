<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Commons\Collection;

/**
 * Organizes individual unique constraint definitions into composite unique groups.
 * Processes unique constraint attributes to create multi-column unique constraints.
 * Groups unique constraints by their uniqueGroup identifier for composite constraint creation.
 */
class UniquesGroups
{
  public Entity|null $entity = null;

  /**
   * Initializes unique constraint groups by processing and organizing unique definitions.
   * Groups unique constraints that share the same uniqueGroup identifier.
   * 
   * @param string $class Fully qualified class name of the entity
   * @param Collection $uniques Collection of individual Uniques objects
   * @param Entity|null $entity Entity metadata object for the owning class
   */
  public function __construct(
    public string $class,
    public Collection $uniques,
    
  ){
    /* Group unique constraints by their uniqueGroup identifier for composite constraints */
    $this->defineGroups();
    /* Create entity metadata for owning class */
    $this->defineEntity();
    /* Clean up temporary data structures */
    $this->defineClears();
  }

  /**
   * Groups unique constraints by their uniqueGroup identifier to create composite constraints.
   * Reduces unique collection into grouped structure where each group contains column names.
   * Enables creation of multi-column unique constraints for data integrity.
   * 
   * @return void Transforms $this->uniques into grouped collection
   */
  private function defineGroups(
  ): void {
    /* Reduce unique constraints into groups based on uniqueGroup identifier */
    $this->uniques = new Collection(
      $this->uniques->reduce( 
      [], 
        function( array|null $acc, Uniques $index ) {
          /* Verify unique is valid Uniques instance */
          if( $index instanceof Uniques ){
            /* Check if property contains Unique attribute definition */
            if( $index->property instanceof Unique ){            
              /* Initialize group collection if not exists */
              if( isset( $acc[ $index->property->uniqueGroup ]) === false ){
                $acc[ $index->property->uniqueGroup ] = new Collection();
              }

              /* Add column name to the appropriate unique constraint group */
              if( isset( $index->property->uniqueGroup )){
                $acc[ $index->property->uniqueGroup ]->add( $index->name );
              }
            }
          }

          /* Return accumulator for next iteration */
          return $acc;
        }
      )
    );
  }

  /**
   * Creates entity metadata object for the class containing these unique constraint groups.
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
   * @return void Unsets class name
   */
  private function defineClears(
  ): void {
    /* Remove class name as it's no longer needed */
    unset( $this->class );
  }
}