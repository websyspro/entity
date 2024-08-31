<?php

namespace Websyspro\Entity\Decorations
{
  use Attribute;

  #[Attribute(Attribute::TARGET_PROPERTY)]
  class Foreign
  {
    public function __construct(
      private string $ReferenceEntity,
      private string $ReferenceKey = "Id"
    ){}

    public function Execute(): array {
      return [
        "foreign" => [
          "entity" => $this->ReferenceEntity,
          "key" => $this->ReferenceKey
        ] 
      ];
    }
  }
}