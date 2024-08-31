<?php

namespace Websyspro\HttpRequest\Decorations\Collumns;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Index
{
  public function __construct(
    private int $Index
  ){}

  public function Execute(): array {
    return [
      "Index" => $this->Index
    ];
  }
}