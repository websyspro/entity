<?php

namespace Websyspro\Entity\Interfaces;

class PrecisionDetails
{
  public function __construct(
    public readonly int $precision,
    public readonly int $scale
  ){}
}