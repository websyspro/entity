<?php

namespace Websyspro\Entity\Shareds;

use ReflectionAttribute;
use ReflectionProperty;

class Property
{

  public function __construct(
    public ReflectionProperty $property,
    public ReflectionAttribute $attribute
  ){}
}