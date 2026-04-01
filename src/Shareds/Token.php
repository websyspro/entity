<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\CompareType;
use Websyspro\Entity\Enums\Type;
use Websyspro\Entity\Enums\MultiLine;
use Websyspro\Commons\Util;

class Token
{
  public int $group;
  public string $value;
  public Type $type;
  public string|null $field = null;
  public Entity|null $entity = null;
  public MultiLine $multiLine; 


  public function __construct(
    string $value,
    int $group,
    Type $type
  ){
    $this->defineInitial(
      $value, 
      $group,
      $type
    );

    $this->defineEntityWithField();
    $this->defineRemoveSingleQuotes();
  }

  private function defineInitial(
    string $value,
    int $group,
    Type $type
  ): void {
    $this->value = $value;
    $this->group = $group;
    $this->type = $type;
  }

  public function setEntity(
    Entity|null $entity
  ): Token {
    $this->entity = $entity;
    return $this;
  }

  public function setField(
    string|null $field
  ): Token {
    $this->field = $field;
    return $this;
  }  

  public function setMultiLine(
    MultiLine $multiLine
  ): Token {
    $this->multiLine = $multiLine;
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
      $this->type = Type::Range;
    } else if( $this->value === CompareType::NotEqual->value && $hasList ){
      $this->value = CompareType::NotIn->value;
      $this->type = Type::Range;
    } else if( $this->value === CompareType::Equals->value && $hasNull ){
      $this->value = CompareType::Is->value;
    } else if( $this->value === CompareType::NotEqual->value && $hasNull ){
      $this->value = CompareType::Not->value;
    }

    return $this;
  }  

  private function defineEntityWithField(
  ): void {
    if( $this->type === Type::Entity ){
      if( $this->entity !== null ){
        $this->value = Util::sprintFormat(
          "%s.%s", [ $this->entity->table, $this->field ]
        );
      }
    }
  }

  private function defineRemoveSingleQuotes(
  ): void {
    $this->value = trim( 
      $this->value, "'"
    );
  }
}