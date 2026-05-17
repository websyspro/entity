<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use Websyspro\Commons\Collection;

class CompareValue
{
  public bool $valueIsList = false;
  public string $value;

  public function __construct(
    Closure $closure,
    Collection $tokens  
  ){
    $this->startupsAnalyzedExtras( $tokens );
    $this->startupsAnalyzed(
      $tokens, $closure
    );
  }

  private function startupsAnalyzedExtras(
    Collection $tokens
  ): void {
    $tokens = $tokens->where( fn( Token $token ) => $token->value !== "." )
      ->mapper( function( Token $token ){ $token->value = trim( $token->value, "'\"" );
        return $token;
      });


    [ $tokenFirst, $tokenLast ] = [
      ...$tokens->slice( 0, 1)->toArray(),
      ...$tokens->slice(-1, 1)->toArray()
    ]; 

    if( $tokenFirst->id === Token::T_UNKNOWN && $tokenLast->id === Token::T_UNKNOWN ){
      if( $tokenFirst->value === Token::T_BRACKET_OPEN && $tokenLast->value === Token::T_BRACKET_CLOSE ){
        $this->valueIsList = true;
      }
    }
  }

  private function startupsAnalyzed(
    Collection $tokens,
    Closure $closure
  ): void {
    $this->startupsAnalyzedNotSimples( $tokens, $closure );
    $this->startupsAnalyzedValues( $tokens );
  }

  private function startupsAnalyzedNotSimples(
    Collection $tokens,
    Closure $closure,
    int $i = 0
  ): void {
    while( $i < $tokens->count()){
      $tokenVar = $tokens->getOneOrFail($i);
      if( $tokenVar instanceof Token ){
        if( $tokenVar->isVariable()){
          $tokenCurlOpen = $tokens->getOneOrFail( $i - 1 );
          if( $tokenCurlOpen instanceof Token ){
            if( $tokenCurlOpen->id === T_CURLY_OPEN ){
              $tokens->spliceOut( $i - 1, 3 );
              $tokens->spliceIn( $i - 1, 0, [ $tokenVar ]);
              $i--; continue;
            }
          }

          $tokens->setValue( 
            $i, $tokenVar->updateVariable(
              $closure
            )
          );          
        }
        if( $tokenVar->isEnumValueWithProperty( $tokens->slice( $i, 5 ))){
          $tokens->setValue(
            $i, $tokenVar->updateEnumValue(
              $closure, $tokens->slice( $i, 5 )
            ) 
          )->spliceOut( $i + 1, 4 );
        } else 
        if( $tokenVar->isEnumValue( $tokens->slice( $i, 3 ))){
          $tokens->setValue(
            $i, $tokenVar->updateEnumValue(
              $closure, $tokens->slice( $i, 3 )
            ) 
          )->spliceOut( $i + 1, 2 );
        }         
      }
      
      $i++;
    }
  }

  private function startupsAnalyzedValues(
    Collection $tokens
  ): void {
    $this->value = $tokens->mapper(fn( Token $token ) => $token->value)
      ->joinNotSpace();
  } 
}