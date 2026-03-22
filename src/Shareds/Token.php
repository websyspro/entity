<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\CompareType;
use Websyspro\Entity\Enums\EntityRoot;
use Websyspro\Entity\Enums\TokenType;
use Websyspro\Commons\Util;

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

  public function setParseCompare(
    Token $token
  ): Token {
    $tokenValue = preg_replace( "#\\\%#", "", $token->value );
    $hasLike = preg_match( "#%#", $tokenValue );
    $hasList = preg_match( "#(^\(.*\)$)#", $tokenValue );
    $hasNull = strtoupper( $tokenValue ) === "NULL";

    if( $this->value === CompareType::Equals->value && $hasLike ){
      $this->value = CompareType::Like->value;
    } else if( $this->value === CompareType::NotEqual->value && $hasLike ){
      $this->value = CompareType::NotLike->value;
    } else if( $this->value === CompareType::Equals->value && $hasList ){
      $this->value = CompareType::In->value;
      $this->type = TokenType::Range;
    } else if( $this->value === CompareType::NotEqual->value && $hasList ){
      $this->value = CompareType::NotIn->value;
      $this->type = TokenType::Range;
    } else if( $this->value === CompareType::Equals->value && $hasNull ){
      $this->value = CompareType::Is->value;
    } else if( $this->value === CompareType::NotEqual->value && $hasNull ){
      $this->value = CompareType::Not->value;
    }

    return $this;
  }  

  private function tokenStart(
  ): void {
    $this->tokenStartParseEntityWithField();
    $this->tokenStartRemoveSingleQuotes();
  }

  private function tokenStartParseEntityWithField(
  ): void {
    if( $this->type === TokenType::Entity ){
      if( $this->entity !== null ){
        $this->value = Util::sprintFormat(
          "%s.%s", [ $this->entity->table, $this->entity->field ]
        );
      }
    }
  }

  private function tokenStartRemoveSingleQuotes(
  ): void {
    $this->value = trim( 
      $this->value, "'"
    );
  }
}