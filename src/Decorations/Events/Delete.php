<?php

namespace Websyspro\Entity\Decorations\Events;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Delete
{
  public AttributeType $attributeType = AttributeType::Delete;
  
  public function __construct(
    public readonly mixed $value
  ){}

  public function get(
  ): mixed {
    if( class_exists( $this->value ) === false){
      return $this->value;
    } else {
      return call_user_func_array(
        [ $this->value, "get" ], []
      );
    }

    return null;
  }
}