<?php

namespace Websyspro\EntityApi;

class EntityConstraint
{
  function __construct(
    public string $Entity,
    public array $Constraints = []
  ){}
}