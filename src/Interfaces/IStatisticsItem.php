<?php

namespace Websyspro\Entity\Interfaces;

class IStatisticsItem
{
  public function __construct(
    public string $name,
    public int $indexGroup
  ){}
}