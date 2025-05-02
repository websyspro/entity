<?php

namespace Websyspro\Entity\Decorations\Events;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Update extends AbstractColumn

{
  public AttributeType $attributeType = AttributeType::Update;
  
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