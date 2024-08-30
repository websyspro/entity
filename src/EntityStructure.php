<?php

namespace Websyspro\Entity
{
  class EntityStructure
  {
    function __construct(
      public string $Entity,
      public array $Properties = []
    ){}
  }
}