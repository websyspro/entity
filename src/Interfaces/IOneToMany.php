<?php

namespace Websyspro\Entity\Interfaces;

class IOneToMany
{
  public function __construct(
    public string $key,
    public string $reference,
    public string $referenceKey
  ){}
}