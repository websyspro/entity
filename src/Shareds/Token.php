<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\CompareType;
use Websyspro\Entity\Interfaces\Entity;
use Websyspro\Entity\Enums\Type;
use Websyspro\Commons\Util;

class Token
{
  public Type $type;
  public Entity|null $entity = null;
  public string|null $value = null;
  public string|null $field = null;
  public string $scope;
  
  public function __construct(
    string|Type $valueOrType
  ){
    $this->startup( $valueOrType );
    $this->startupTypeString();
  }

  private function startup(
    string|Type $valueOrType
  ): void {
    if( $valueOrType instanceof Type ){
      $this->value = $valueOrType->name;
      $this->type = $valueOrType;
    } else {
      $this->type = $this->getTokenByValue(
        $this->value = $valueOrType
      ); 
    }
  }

  private function startupTypeString(
  ): void {
    if( $this->type === Type::String ){
      $this->value = Util::replace(
        [ "#(^'|'$)#", "#(^\\\"|\\\"$)#" ], $this->value
      );
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

  public function defineType(
    Type $type
  ): Token {
    $this->type = $type;
    return $this;
  }

  public function defineScope(
    string $scope
  ): Token {
    $this->scope = $scope;
    return $this;
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

  public function defineReverseFromValueCompare(
    Token $token
  ): Token {
    $hasLike = Util::match( "#%#", Util::replace( "#\\\%#", $token->value ));
    $hasList = Util::match( "#(^\(.*\)$)#", Util::replace( "#\\\%#", $token->value ));
    $hasNull = strtoupper( Util::replace( "#\\\%#", $token->value )) === "NULL";
    
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

  public function getField(): string|null {
    if( Util::match( "#->#", $this->value ) === false){
      return $this->field;
    }

    [ $_, $field ] = explode( "->", $this->value );
    return $field;
  }
  
  public function setEntity(
    Entity $entity,
    array $alias
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