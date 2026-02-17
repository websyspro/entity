<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

/**
 * Represents the complete metadata structure of an entity for ORM operations.
 * Aggregates all database-related information including columns, constraints, relationships, and indexes.
 * Provides organized access to entity schema for migration generation, validation, and query building.
 */
class EntityStructure
{
  /**
   * Initializes entity structure with all metadata components.
   * Automatically indexes collections by property name for efficient lookup operations.
   * 
   * @param Collection $columns Database column definitions with types and constraints
   * @param IndexesGroups $indexesGroups Grouped index definitions for query optimization
   * @param UniquesGroups $uniquesGroups Grouped unique constraint definitions for data integrity
   * @param Collection $foreigns Foreign key relationships for referential integrity
   * @param Collection $primaryKey Primary key column(s) for entity identification
   * @param Collection $requireds Required field definitions for validation
   * @param Collection $oneToMany One-to-many relationship mappings for ORM
   * @param Collection $oneToOne One-to-one relationship mappings for ORM
   */
  public function __construct(
    public Collection $columns,
    public IndexesGroups $indexesGroups,
    public UniquesGroups $uniquesGroups,
    public Collection $foreigns,
    public Collection $primaryKey,
    public Collection $requireds,
    public Collection $oneToMany,
    public Collection $oneToOne
  ){
    /* Transform collections to use property names as keys for O(1) lookup performance */
    $this->defineKeyForName( $this->columns );
    $this->defineKeyForName( $this->foreigns );
    $this->defineKeyForName( $this->primaryKey );
    $this->defineKeyForName( $this->requireds );
    $this->defineKeyForName( $this->oneToMany );
    $this->defineKeyForName( $this->oneToOne );
  }

  /**
   * Converts indexed collection to associative array keyed by property name.
   * Enables direct property access by name instead of iterating through collection.
   * Improves lookup performance from O(n) to O(1) for property-based queries.
   * 
   * @param Collection &$items Collection to be re-indexed (passed by reference for in-place modification)
   * @param array $newItems Accumulator array for building name-keyed structure
   * @return void Modifies $items collection in-place
   */
  private function defineKeyForName(
    Collection &$items,
    array $newItems = []
  ): void {
    /* Build associative array mapping property names to their metadata objects */
    foreach( $items->all() as $item ){
      $newItems[ $item->name ] = $item;
    }

    /* Replace original collection with name-indexed version for efficient access */
    $items = new Collection( 
      $newItems
    );
  }  
}