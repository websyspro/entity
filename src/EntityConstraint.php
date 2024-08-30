<?php

namespace Websyspro\Entity
{
  class EntityConstraint
  {
    function __construct(
      public string $Entity,
      public array $Constraints = []
    ){}
  }
}