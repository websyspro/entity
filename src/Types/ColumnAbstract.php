<?php

namespace Websyspro\Entity\Types;

class ColumnAbstract
{
  public function count(): self {
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

  public function trim(
  ): self {
    return $this;
  }

  public function upper(
  ): self {
    return $this;
  }

  public function lower(
  ): self {
    return $this;
  }

  public function isNull(
  ): self {
    return $this;
  }
  
  public function isNotNull(
  ): self {
    return $this;
  }  

  public function contains(
    mixed ...$args
  ): self {
    return $this;
  }

  public function startsWith(
    mixed ...$args
  ): self {
    return $this;
  }
  public function endsWith(
    mixed ...$args
  ): self {
    return $this;
  }

  public function in(
    mixed ...$args
  ): self {
    return $this;
  }  
}