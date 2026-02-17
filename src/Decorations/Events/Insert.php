<?php

namespace Websyspro\Entity\Decorations\Events;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Insert
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::insert;
  
  public function __construct(
    public readonly mixed $value
  ){}

  public function Get(
  ): mixed {
    if(class_exists($this->value) === false){
      return $this->value;
    } else {
      return call_user_func_array(
        [$this->value, "Get"], []
      );
    }

    return null;
  }
}