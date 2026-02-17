<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Decorations\Statistics\Index;
use Websyspro\Commons\Collection;

/**
 * Organizes individual index definitions into composite index groups.
 * Processes index attributes to create multi-column indexes for query optimization.
 * Groups indexes by their indexGroup identifier for composite index creation.
 */
class IndexesGroups
{
  public Entity|null $entity = null;

  /**
   * Initializes index groups by processing and organizing index definitions.
   * Groups indexes that share the same indexGroup identifier.
   * 
   * @param string $class Fully qualified class name of the entity
   * @param Collection $indexes Collection of individual Indexes objects
   * @param Entity|null $entity Entity metadata object for the owning class
   */
  public function __construct(
    public string $class,
    public Collection $indexes
  ){
    /* Group indexes by their indexGroup identifier for composite indexes */
    $this->defineGroups();
    /* Create entity metadata for owning class */
    $this->defineEntity();
    /* Clean up temporary data structures */
    $this->defineClears();
  }

  /**
   * Groups indexes by their indexGroup identifier to create composite indexes.
   * Reduces index collection into grouped structure where each group contains column names.
   * Enables creation of multi-column indexes for complex query optimization.
   * 
   * @return void Transforms $this->indexes into grouped collection
   */
  private function defineGroups(
  ): void {
    /* Reduce indexes into groups based on indexGroup identifier */
    $this->indexes = new Collection(
      $this->indexes->reduce( 
      [], 
        function( array|null $acc, Indexes $index ) {
          /* Verify index is valid Indexes instance */
          if( $index instanceof Indexes ){
            /* Check if property contains Index attribute definition */
            if( $index->property instanceof Index ){            
              /* Initialize group collection if not exists */
              if( isset( $acc[ $index->property->indexGroup ]) === false ){
                $acc[ $index->property->indexGroup ] = new Collection();
              }

              /* Add column name to the appropriate index group */
              if( isset( $index->property->indexGroup )){
                $acc[ $index->property->indexGroup ]->add( $index->name );
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
   * Creates entity metadata object for the class containing these index groups.
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