<?php

namespace Websyspro\Entity\Shareds;

class TokenEntity
{
  public function __construct(
    public Entity $entity,
    public string $field
  ){}
}