<?php

namespace Websyspro\Entity;

/**
 * Alias para BaseIncrementEntity — mantido para compatibilidade.
 * Use BaseIncrementEntity ou BaseUUIDEntity diretamente.
 */
abstract class BaseEntity
{
  public function sum(
    mixed $expr
  ): void {}
}
