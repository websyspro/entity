<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

class CompareValue
{
  public bool $valueIsList = false;
  public string $value;

  public function __construct(
    public ExpressionCompare $expressionCompare,
    public Collection $tokens  
  ){
    $this->startupsAnalyzedExtras();
    $this->startupsAnalyzed();
    $this->startupsClear();
  }

  private function startupsAnalyzedExtras(
  ): void {
    $this->tokens = $this->tokens
      ->where( fn( Token $token ) => $token->value !== "." )
      ->mapper( function( Token $token ){ $token->value = trim( $token->value, "'\"" );
        return $token;
      });

    [ $tokenFirst, $tokenLast ] = [
      ...$this->tokens->slice( 0, 1)->toArray(),
      ...$this->tokens->slice(-1, 1)->toArray()
    ]; 

    if( $tokenFirst->id === Token::T_UNKNOWN && $tokenLast->id === Token::T_UNKNOWN ){
      if( $tokenFirst->value === Token::T_BRACKET_OPEN && $tokenLast->value === Token::T_BRACKET_CLOSE ){
        $this->valueIsList = true;
      }
    }
  }

  private function startupsAnalyzed(
  ): void {
    $this->startupsAnalyzedNotSimples();
    $this->startupsAnalyzedValues();
  }

  private function startupsAnalyzedNotSimples(
    int $i = 0
  ): void {
    while( $i < $this->tokens->count()){
      $tokenVar = $this->tokens->getOneOrFail($i);
      if( $tokenVar instanceof Token ){
        if( $tokenVar->isVariable()){
          $tokenCurlOpen = $this->tokens->getOneOrFail( $i - 1 );
          if( $tokenCurlOpen instanceof Token ){
            if( $tokenCurlOpen->id === T_CURLY_OPEN ){
              $this->tokens->spliceOut( $i - 1, 3 );
              $this->tokens->spliceIn( $i - 1, 0, [ $tokenVar ]);
              $i--; continue;
            }
          }

          $this->tokens->setValue( 
            $i, $tokenVar->updateVariable(
              $this->expressionCompare
            )
          );          
        }
        if( $tokenVar->isEnumValueWithProperty( $this->tokens->slice( $i, 5 ))){
          $this->tokens->setValue(
            $i, $tokenVar->updateEnumValue(
              $this->expressionCompare, 
              $this->tokens->slice( $i, 5 )
            ) 
          )->spliceOut( $i + 1, 4 );
        } else 
        if( $tokenVar->isEnumValue( $this->tokens->slice( $i, 3 ))){
          $this->tokens->setValue(
            $i, $tokenVar->updateEnumValue(
              $this->expressionCompare, 
              $this->tokens->slice( $i, 3 )
            ) 
          )->spliceOut( $i + 1, 2 );
        }         
      }
      
      $i++;
    }
  }

  private function startupsAnalyzedValues(
  ): void {
    $this->value = $this->tokens->mapper( fn( Token $token ) => $token->value )
      ->joinNotSpace();
  }
  
  private function startupsClear(
  ): void {
    unset( $this->expressionCompare, $this->tokens );
  }  
}