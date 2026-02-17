<?php

namespace Websyspro\Entity\Shareds;

/**
 * Represents entity metadata extracted from entity class name.
 * Provides simplified entity name by removing namespace and "Entity" suffix.
 * Used for table naming, relationship mapping, and schema generation.
 */
class Entity
{
  public string $name;
  
  /**
   * Initializes entity metadata by extracting entity name from fully qualified class name.
   * 
   * @param string $class Fully qualified class name of the entity
   */
  public function __construct(
    public string $class
  ){
    /* Extract and format entity name from class name */
    $this->defineName();
  }

  /**
   * Extracts entity name from fully qualified class name.
   * Removes namespace prefix and "Entity" suffix to get clean entity name.
   * Used for database table naming and relationship identification.
   * 
   * @return void Sets $this->name with extracted entity name
   */
  private function defineName(
  ): void {
    /* Split class name by namespace separator to get class parts */
    $entity = preg_split( 
      "#\\\#", 
      $this->class
    );

    /* Remove "Entity" suffix from last part to get clean entity name */
    $this->name = preg_replace( 
      "#Entity$#", 
      "", end( 
        $entity
      )
    );
  }
}