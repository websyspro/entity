<?php

namespace Websyspro\EntityApi;

class EntityStructure
{
  function __construct(
    public string $Entity,
    public array $Properties = [],
    public array $Constraints = []
  ){}
}