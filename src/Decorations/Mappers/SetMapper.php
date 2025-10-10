<?php

namespace Websyspro\Entity\Decorations\Mappers;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class SetMapper
{
  public function __construct(
    private string $sourceClass,
    private string $targetClass
  ){}

  public function mapper(
  ): array {
    return [
      $this->sourceClass,
      $this->targetClass
    ];
  }
}