<?php

namespace Websyspro\Entity\Decorations
{
  use Attribute;

  #[Attribute(Attribute::TARGET_PROPERTY)]
  class BigInt
  {
    public function __construct(){}
  
    public function Execute(): array {
      return [
        "type" => "bigint"
      ];
    }
  }
}