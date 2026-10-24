<?php

namespace Websyspro\Entity\Decorators;

use Attribute;
use Closure;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EntityList
implements \JsonSerializable
{
  private array $items = [];

  public function __construct(
    public readonly string $entity = ''
  ){}

  public function __set(
    string $name,
    mixed $value
  ): void {
    $this->$name = $value;
  }

  public function __get(
    string $name
  ): mixed {
    return $this->$name ?? null;
  }

  public function setValue(
    array $value
  ): static {
    $this->items = $value;
    return $this;
  }

  public function getValue(
  ): array {
    return $this->items;
  }

  public function jsonSerialize(
  ): array {
    return $this->items;
  }

  public function any(
    Closure $arrow
  ): void {}

  public function all(
    Closure $arrow
  ): void {}

  public function none(
    Closure $arrow
  ): void {}

  public function where(
    Closure $closure
  ): void {}

  public function select(
    Closure $closure
  ): void {}  
  
  public function include(
    mixed ...$closure
  ): void {}  
}
