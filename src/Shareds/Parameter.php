<?php

namespace Websyspro\Entity\Shareds;

class Parameter
{
  public function __construct(
    public string $name,
    public EntityStructure|null $entityStructure = null
  ){}    
}