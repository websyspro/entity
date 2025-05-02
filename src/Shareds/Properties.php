<?php

namespace Websyspro\Entity\Shareds;

use ReflectionAttribute;
use Websyspro\Commons\TList;

class Properties
{
  public function __construct(
    public string $name,
    public TList $properties
  ){
    $properties->Mapper(
      fn(ReflectionAttribute $ra ) => $ra->newInstance()
    );
  }
}