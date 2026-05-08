<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Util;
use Websyspro\Entity\Enums\CompareType;
use Websyspro\Entity\Enums\Type;
use Websyspro\Entity\Interfaces\Entity;

class Token
{
  public Type $type;
  public Entity|null $entity = null;
  public string|null $value = null;
  public string|null $field = null;
  public int $group;
  
  public function __construct(
    string $value
  ){
    $this->startup( $value );
    $this->startupTypeString( $value );
  }

  private function startup(
    string $value
  ): void {
    $this->type = $this->getTokenByValue(
      $this->value = $value
    );
  }

  private function startupTypeString(
    string $value
  ): void {
    if( $this->type === Type::String ){
      $this->value = Util::replace([ "#(^'|'$)#", "#(^\\\"|\\\"$)#" ], $this->value);
    }
  }

  private function getTokenByValue(
    string $value
  ): Type {
    if( preg_match( "#(=|==|===|<>|!=|!==|>=|<=|Between)#", $value )){
      return Type::Compare;
    } else
    if( preg_match( "#^(!)?\\\$.*->.*$#", $value )){
      return Type::Entity;
    } else
    if( preg_match( "#\\\$(\{[a-zA-Z_][a-zA-Z0-9_]*\}|[a-zA-Z_][a-zA-Z0-9_]*)#", $value )){
      return Type::Static;
    } else
    if( preg_match( "#^(\\\"|').*(\\\"|')$#", $value )){
      return Type::String;
    } else
    if( preg_match( "#(&&|\|\||And|Or)#", $value )){
      return Type::Logical;
    }else
    if( preg_match( "#^[a-zA-Z]{1}.*::.*(->(?:name|value))?$#", $value )){
      return Type::Enum;
    } else
    if(preg_match( "#^,$#", $value )){
      return Type::Ignore;
    } else
    if( preg_match( "#^\($#", $value )){
      return Type::StartGroup;
    } else 
    if( preg_match( "#^\)$#", $value )){
      return Type::EndGroup;
    } else return Type::String;
  }
  
  public function defineReverseCompare(
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

  public function getField(): string|null {
    if( Util::match( "#->#", $this->value ) === false){
      return $this->field;
    }

    [ $_, $field ] = explode( "->", $this->value );
    return $field;
  }
  
  public function setEntity(
    Entity $entity
  ): Token {
    $this->entity = $entity;
    $this->field = $this->getField();

    $this->value = Util::sprintFormat( "%s.%s", [
      $this->entity->alias, $this->field
    ]);

    return $this;
  }

  public function setEntityForToken(
    Token $token
  ): Token {
    $this->entity = $token->entity;
    $this->field = $token->field;
    return $this;
  }  

  public function isEntity(): bool {
    return $this->type === Type::Entity;
  }

  public function isString(): bool {
    return $this->type === Type::String;
  } 
  
  public function isEntityOrString(): bool {
    return $this->type === Type::Entity || $this->type === Type::Entity;
  }  

  public function isLogical(): bool {
    return $this->type === Type::Logical;
  }

  public function isCompare(): bool {
    return $this->type === Type::Compare;
  }  
}