<?php

namespace Websyspro\Entity\Shareds;

use CollectionionAttribute;
use Websyspro\Commons\Collection;

class Properties
{
  public function __construct(
    public string $name,
    public Collection $items
  ){
    $this->items->Mapper(
      fn(CollectionionAttribute $ra ) => $ra->newInstance()
    );
  }
}