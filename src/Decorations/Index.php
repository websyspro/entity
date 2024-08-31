<?php

namespace Websyspro\Entity\Decorations
{
  use Attribute;

  #[Attribute(Attribute::TARGET_PROPERTY)]
  class Index
  {
    public function __construct(
      private int $Index
    ){}
  
    public function Execute(): array {
      return [
        "index" => $this->Index
      ];
    }
  }
}