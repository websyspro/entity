<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionAttribute;
use ReflectionProperty;

/**
 * Wraps ReflectionProperty to provide simplified access to property metadata.
 * Extracts property name and PHP 8 attributes for entity metadata processing.
 * Converts reflection attributes into instantiated attribute objects.
 */
class Property
{
  public string $name;
  public Collection $attributes;

  /**
   * Initializes property wrapper by extracting name and attributes.
   * 
   * @param ReflectionProperty $property Reflection object representing entity property
   */
  public function __construct(
    public ReflectionProperty $property
  ){
    /* Extract property name from reflection */
    $this->defineName();
    /* Extract and instantiate PHP 8 attributes */
    $this->defineAttributes();
    /* Clean up reflection object to save memory */
    $this->defineClears();
  }

  /**
   * Extracts property name from ReflectionProperty.
   * 
   * @return void Sets $this->name with property name
   */
  private function defineName(
  ): void {
    /* Copy property name from reflection object */
    $this->name = $this->property->name;
  }

  /**
   * Extracts and instantiates PHP 8 attributes attached to the property.
   * Converts ReflectionAttribute objects into actual attribute instances.
   * 
   * @return void Sets $this->attributes with collection of instantiated attributes
   */
  private function defineAttributes(
  ): void {
    /* Map reflection attributes to instantiated attribute objects */
    $this->attributes = new Collection(
      Util::mapper(
        $this->property->getAttributes(),
        fn( ReflectionAttribute $attribute ) => $attribute->newInstance()
      )
    );
  }

  /**
   * Releases reflection object to optimize memory usage.
   * 
   * @return void Unsets reflection property
   */
  private function defineClears(
  ): void {
    /* Remove reflection object as it's no longer needed */
    unset( 
      $this->property
    );
  }  
}