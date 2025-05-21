<?php

namespace Websyspro\Entity\Shareds;

use ReflectionAttribute;
use Websyspro\Commons\Collection;

class Properties
{
  public function __construct(
    public string $name,
    public Collection $items
  ){
    $this->items->Mapper(
      fn(ReflectionAttribute $ra ) => $ra->newInstance()
    );
  }
}