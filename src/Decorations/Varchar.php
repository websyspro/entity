<?php

namespace Websyspro\Decorations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Varchar 
{
  public function __construct(
    private int $size = 0
  ){}

  public function get(): array {
    return [
      "type" => "varchar({$this->size})"
    ];
  }
}