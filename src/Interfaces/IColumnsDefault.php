<?php

namespace Websyspro\Entity\Interfaces;

class IColumnsDefault
{
  public function __construct(
    public array $initials,
    public array $ends
  ){}
}