<?php

namespace Websyspro\Entity\Interfaces;

class IStatisticsNamesItem
{
  public function __construct(
    public string $name,
    public string $columns
  ){}
}