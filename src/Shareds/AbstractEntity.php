<?php

namespace Websyspro\Entity\Shareds;

class AbstractEntity
{
  public function include(
    callable|null $fn = null
  ): AbstractEntity {
    return $this;
  }

public function sum(
    mixed $mixed = null
  ): self {
    return $this;
  }  

  public function max(): self {
    return $this;
  }

  public function min(): self {
    return $this;
  }
  
  public function avg(): self {
    return $this;
  }  
}