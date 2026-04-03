<?php

namespace Websyspro\Entity\Decorations\Events;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;

/**
 * PHP attribute for defining default value on entity property during DELETE operations (soft delete).
 * Automatically sets property value when entity is soft deleted.
 * Supports both static values and dynamic values from callable classes.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class Delete
{
  public AttributeType $attributeType = AttributeType::delete;
  
  /**
   * Initializes delete event with default value or value generator class.
   * 
   * @param mixed $value Static value or class name with Get() method for dynamic values
   */
  public function __construct(
    public readonly mixed $value
  ){}

  /**
   * Retrieves the value to be used during delete operation.
   * Calls Get() method if value is a class, otherwise returns static value.
   * 
   * @return mixed Value to set on property during soft delete
   */
  public function Get(
  ): mixed {
    /* Return static value if not a class */
    if(class_exists($this->value) === false){
      return $this->value;
    } else {
      /* Call Get() method on value generator class for dynamic values */
      return call_user_func_array(
        [$this->value, "Get"], []
      );
    }

    return null;
  }
}