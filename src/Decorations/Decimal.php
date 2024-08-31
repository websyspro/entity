<?php

namespace Websyspro\Entity\Decorations
{
  use Attribute;

  #[Attribute(Attribute::TARGET_PROPERTY)]
  class Decimal
  {
    public function __construct(){}

    public function Execute(): array {
      return [
        "type" => "decimal(10,4)"
      ];
    }
  }
}