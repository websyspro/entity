<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

/**
 * Represents referenced entity metadata in foreign key relationships.
 * Extracts primary key information from target entity for relationship mapping.
 * Enables foreign key constraint creation by identifying target entity's primary key.
 */
class ReferenceClass
{
  public Collection $primaryKey;

  /**
   * Initializes reference class metadata by extracting target entity's primary key.
   * Calls target entity's getAttributes method to retrieve entity structure.
   * 
   * @param string $class Fully qualified class name of the referenced entity
   */
  public function __construct(
    public string $class
  ){
    /* Extract primary key information from referenced entity */
    $this->defineReference();
    /* Clean up temporary data structures */
    $this->defineClears();
  }

  /**
   * Retrieves entity structure from referenced class and extracts primary key.
   * Dynamically calls getAttributes method on target entity to get metadata.
   * Stores primary key collection for foreign key constraint creation.
   * 
   * @return void Sets $this->primaryKey with target entity's primary key collection
   */
  private function defineReference(
  ): void {
    /* Call getAttributes method on referenced entity class to get structure */
    $entityStructure = call_user_func_array(
      [ $this->class, "getAttributes" ], []
    );

    /* Extract primary key from entity structure if valid */
    if( $entityStructure instanceof EntityStructure ){
      $this->primaryKey = $entityStructure->primaryKey;
    }
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