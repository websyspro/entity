<?php

namespace Websyspro\Entity\Decorations\Events;

use Attribute;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class TDelete extends TAbstractColumn
{
  public TAttributeType $attributeType = TAttributeType::Delete;
  
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