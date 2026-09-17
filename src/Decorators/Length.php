<?php

namespace Websyspro\Entity\Decorators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Length
{
    public function __construct(
        public readonly int $value = 255
    ) {}
}
