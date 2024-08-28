<?php

namespace Websyspro\Decorations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Foreign
{
  public function __construct(
    private string $ReferenceEntity,
    private string $ReferenceKey = "Id"
  ){}

  public function get(): array {
    return [
      "Foreign" => [
        "ReferenceEntity" => $this->ReferenceEntity,
        "ReferenceKey" => $this->ReferenceKey
      ] 
    ];
  }
}