<?php

namespace Websyspro\Entity\Decorations
{
  use Attribute;

  #[Attribute(Attribute::TARGET_PROPERTY)]
  class Varchar 
  {
    public function __construct(
      private int $size = 0
    ){}

    public function Execute(): array {
      return [
        "type" => "varchar({$this->size})"
      ];
    }
  }
}