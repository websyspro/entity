<?php

namespace Websyspro\Decorations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Date
{
  public function __construct(){}

  public function get(): array {
    return [
      "type" => "date"
    ];
  }
}