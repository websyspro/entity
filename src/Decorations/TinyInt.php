<?php

namespace Websyspro\Entity\Decorations
{
  use Attribute;

  #[Attribute(Attribute::TARGET_PROPERTY)]
  class TinyInt
  {
    public function __construct(){}

    public function Execute(): array {
      return [
        "type" => "tinyint"
      ];
    }
  }
}