<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\TokenType;
use Websyspro\Commons\Collection;

/**
 * Representa um token identificado no corpo de uma arrow function
 * 
 * Armazena informações sobre um elemento da expressão que será convertida em SQL,
 * incluindo seu tipo, valor e metadados da entidade associada
 */
class Token
{
  /**
   * @param TokenType $takenType Tipo do token (FieldEntity, Compare, Logical, etc)
   * @param string $value Valor do token
   */
  public function __construct(
    public TokenType $type,
    public array $value,
    public TokenEntity|null $entity = null,
    public int|null $group = null,
    public int|null $order = null,
    public int|null $joinOrder = null
  ){}
}