<?php

namespace Websyspro\Entity\Decorators;

use Websyspro\ArrowToSql\Repository;
use Websyspro\Utils\Collection;
use Attribute;
use Closure;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EntityList
{
  public function __construct(
    private string $entity = "",
    public array $items = []
  ){}

  public function getEntity(): string {
    return $this->entity;
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
    Closure|Repository $closure
  ): EntityList {
    return $this;
  }  

  public function as(
    string $alias
  ): void {}  
  
  public function include(
    mixed ...$closure
  ): void {} 
  
  public function mapper(
    Closure $closure
  ): EntityList {
    return new EntityList(
      $this->entity, array_map( 
        $closure, $this->items
      )
    );
  }   
}
