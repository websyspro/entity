<?php

namespace Websyspro\Entity\Interfaces;

class IOneToOne
{
  public function __construct(
    public string $key,
    public string $reference,
    public string $referenceKey
  ){}
}