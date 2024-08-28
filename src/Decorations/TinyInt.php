<?php

namespace Websyspro\EntityApi\Decorations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TinyInt
{
  public function __construct(){}

  public function get(): array {
    return [
      "type" => "tinyint"
    ];
  }
}