<?php

namespace Websyspro\Entity\Interfaces;

class ParameterQuery
{
  public function __construct(
    public array $primaryKey,
    public Entity $entity,
    public Entity|null $entityParent = null,
  ){}
}