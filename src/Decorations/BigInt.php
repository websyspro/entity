<?php

namespace Websyspro\EntityApi\Decorations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BigInt
{
  public function __construct(){}

  public function get(): array {
    return [
      "type" => "bigint"
    ];
  }
}