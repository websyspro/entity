<?php

namespace Websyspro\Entity\Decorations
{
  use Attribute;

  #[Attribute(Attribute::TARGET_PROPERTY)]
  class Required
  {
    public function __construct(){}

    public function Execute(): array {
      return [
        "required" => "sim"
      ];
    }
  }
}