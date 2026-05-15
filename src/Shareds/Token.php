<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use Websyspro\Entity\Decorations\Columns\Enum;

class Token
{
  public int $id;
  public string $type;
  public string $value;

  public const int T_UNKNOWN = -1;
  public const int T_FIELD_AND_FIELD = 1;
  public const int T_FIELD_AND_VALUE = 2;
  public const int T_VALUE_AND_FIELD = 3;
  public const int T_VALUE_AND_VALUE = 4;
  public const string T_PARENTHESES_OPEN = "(";
  public const string T_PARENTHESES_CLOSE = ")";
  public const string T_BRACKET_OPEN = "[";
  public const string T_BRACKET_CLOSE = "]";
  public const string T_SEMICOLON = ";";
  public const string T_COMMA = ",";

  public const array T_COMPARE_LIST = [
    T_IS_EQUAL, T_IS_IDENTICAL,
    T_IS_NOT_EQUAL, T_IS_NOT_IDENTICAL,
    T_IS_GREATER_OR_EQUAL, T_IS_SMALLER_OR_EQUAL
  ];

  public function __construct(
    array|string $tokenArr
  ){
    $this->startups(
      $tokenArr
    );
  }

  public function isWhiteSpace(
  ): bool {
    return $this->id === T_WHITESPACE;
  }

  public function isVariable(
  ): bool {
    return $this->id === T_VARIABLE;
  }

  public function updateVariable(
    ExpressionCompare $expressionCompare
  ): Token {
    $statics = ClosureUtil::getStatics(
      $expressionCompare->closure
    );

    if( $statics->exist() ){
      $this->id = T_STRING;
      $this->type = token_name( T_STRING ); 
      $this->value = $statics->getOneOrFail(
        ltrim( $this->value, "$" )
      );
    }

    return $this;
  }

  public function isEnumValue(
    Collection $tokensEnum
  ): bool {
    if( $tokensEnum->count() < 3 ){
      return false;
    }

    if( $tokensEnum->count() === 3 ){
      [ $enum, $doubleColon, $case ] = $tokensEnum->toArray();
      if( $enum instanceof Token && $doubleColon instanceof Token && $case instanceof Token ){
        return $enum->id === T_STRING && $doubleColon->id === T_DOUBLE_COLON && $case->id === T_STRING;
      }
    }

    return false;
  }

  public function isEnumValueWithProperty(
    Collection $tokensEnum
  ): bool {
    if( $tokensEnum->count() < 5 ){
      return false;
    }

    if( $tokensEnum->count() === 5 ){
      [ $enum, $doubleColon, $case, $objectOperator, $property ] = $tokensEnum->toArray();
      if( $enum instanceof Token && $doubleColon instanceof Token && $case instanceof Token && $property instanceof Token ){
        return $enum->id === T_STRING 
            && $doubleColon->id === T_DOUBLE_COLON 
            && $case->id === T_STRING 
            && $objectOperator->id === T_OBJECT_OPERATOR 
            && $enum->id === T_STRING;        
      } 
    }

    return false;
  } 

  public function updateEnumValue(
    ExpressionCompare $expressionCompare,
    Collection $tokensEnum   
  ): Token {
    if( $tokensEnum->exist() === false ){
      return $this;
    }

    if( $tokensEnum->count() === 5 ){
      [ $alias, $_, $case, $_, $property ] = $tokensEnum->toArray();
    } else if( $tokensEnum->count() === 3 ){
      [ $alias, $_, $case ] = $tokensEnum->toArray();
    }

    $uses = ClosureUtil::getUses( $expressionCompare->closure );
    if( $uses instanceof Uses ){
      $useslist = $uses->list->where( 
        fn( UsesItem $usesItem ) => $usesItem->alias === $alias->value 
      );

      if( $useslist->exist()){
        [ $useslist ] = $useslist->toArray();

        $constanteEnum = Util::sprintFormat( "%s::%s", [
          $useslist->path, $case->value 
        ]);

        
        if( defined( $constanteEnum )){
          $enumCase = constant( $constanteEnum );
          if( isset( $enumCase )){
            if( isset( $property )){
              if( $property->value === "name" ){
                $this->value = $enumCase->name;
              } else if( $property->value === "value" ){
                $this->value = $enumCase->value;
              }
            } else {
              $this->value = $enumCase->value;
            };
          }
        }
      }
    }

    return $this;
  }
  
  private function startups(
    array|string $tokenArr
  ): void {
    Util::isArray( $tokenArr )
      ? $this->defineToken( $tokenArr )
      : $this->defineTokenStr( $tokenArr );
  }

  private function defineTokenName(
    int $id
  ): string {
    return $id !== static::T_UNKNOWN
      ? token_name( $this->id ) : "T_UNKNOWN";
  }

  private function defineToken(
    array|string $tokenArr
  ): void {
    [ $this->id, $this->value ] = $tokenArr;
    $this->type = $this->defineTokenName( $this->id );
  }

  private function defineTokenStr(
    string $tokenArr
  ): void {
    [ $this->id, $this->value ] = [ static::T_UNKNOWN, $tokenArr ];
    $this->type = $this->defineTokenName( $this->id );
  }
}