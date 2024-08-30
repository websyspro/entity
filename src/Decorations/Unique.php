<?php

namespace Websyspro\Entity\Decorations
{
  use Attribute;

  #[Attribute(Attribute::TARGET_PROPERTY)]
  class Unique
  {
    public function __construct(
      private int $Unique
    ){}

    public function get(): array {
      return [
        "Unique" => $this->Unique
      ];
    }
  }
}