<?php

namespace Websyspro\Decorations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AutoInc
{
  public function __construct(){}

  public function get(): array {
    return [
      "autoinc" => "sim"
    ];
  }
}