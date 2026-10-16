<?php

namespace Websyspro\Entity;

use Closure;

/**
 * Alias para BaseIncrementEntity — mantido para compatibilidade.
 * Use BaseIncrementEntity ou BaseUUIDEntity diretamente.
 */
abstract class BaseEntity
{
  public function as(
    string $alias    
  ): void {}

  public function sum(
    mixed $expr
  ): void {}

  public function where(
    Closure $closure
  ): void {}

  public function select(
    Closure $closure
  ): void {}  
}
