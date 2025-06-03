<?php

namespace Websyspro\Entity\Shareds;

use ReflectionAttribute;
use Websyspro\Commons\TList;

class TProperties
{
  public function __construct(
    public string $name,
    public TList $items
  ){
    $this->items->Mapper(
      fn(ReflectionAttribute $ra ) => $ra->newInstance()
    );
  }
}