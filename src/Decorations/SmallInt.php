<?php

namespace Websyspro\Entity\Decorations
{
  use Attribute;

  #[Attribute(Attribute::TARGET_PROPERTY)]
  class SmallInt
  {
    public function __construct(){}

    public function get(): array {
      return [
        "type" => "smallint"
      ];
    }
  }
}