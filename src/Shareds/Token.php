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
  public Entity $entity;
  public string $fieldName;

  /**
   * @param TokenType $takenType Tipo do token (FieldEntity, Compare, Logical, etc)
   * @param string $value Valor do token
   */
  public function __construct(
    public TokenType $takenType,
    public Collection $tokenValue
  ){}

  public function setEntity(
    Parameter $parameter,
    string $fieldName
  ): Token {
    $this->entity = $parameter->entityStructure->entity;
    $this->fieldName = $fieldName;
    return $this;
  }
}