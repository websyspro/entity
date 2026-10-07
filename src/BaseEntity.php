<?php

namespace Websyspro\Entity;

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
}
