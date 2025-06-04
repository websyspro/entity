<?php

namespace Websyspro\Entity\Interfaces;

use ReflectionAttribute;
use Websyspro\Commons\DataList;

class IProperties
{
  public function __construct(
    public string $name,
    public DataList $items
  ){
    $this->items->Mapper(
      fn(ReflectionAttribute $ra ) => $ra->newInstance()
    );
  }
}