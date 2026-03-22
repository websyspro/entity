<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\CompareType;
use Websyspro\Entity\Enums\EntityRoot;
use Websyspro\Entity\Enums\TokenType;

class Token
{
  public function __construct(
    public TokenType $type,
    public string $value,
    public int $group,
    public TokenEntity|null $entity = null,
    public EntityRoot|null $root = null
  ){
    $this->tokenStart();
  }

  public function setEntity(
    TokenEntity $entity
  ): Token {
    $this->entity = $entity;
    return $this;
  }  

  public function setRoot(
    EntityRoot $root
  ): Token {
    $this->root = $root;
    return $this;
  }

  public function setInvertCompare(
  ): Token {
    $this->value = match( CompareType::tryFrom( $this->value ) ){
      CompareType::GreaterEqual => CompareType::LessEqual->value, 
      CompareType::LessEqual => CompareType::GreaterEqual->value, 
      CompareType::Greater => CompareType::Less->value, 
      CompareType::Less => CompareType::Greater->value, 
        default => $this->value
    };

    return $this;
  }

  private function setValueFromEntity(
  ): void {
    // if( $this->type === TokenType::Entity ){
    //   if( $this->entity !== null ){
    //     $this->value = Util::sprintFormat(
    //       "%s.%s", [ $this->entity->table, $this->entity->field ]
    //     );
    //   }
    // }
  }

  private function tokenStart(
  ): void {
    $this->tokenStartRemoveSingleQuotes();
  }

  private function tokenStartRemoveSingleQuotes(
  ): void {
    $this->value = trim( 
      $this->value, "'"
    );
  }
}