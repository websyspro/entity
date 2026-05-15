<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

class CompareValue
{
  public function __construct(
    public ExpressionCompare $expressionCompare,
    public Collection $tokens  
  ){
    $this->startupsAnalyzed();
    $this->startupsClear();
  }

  private function startupsAnalyzed(
  ): void {
    if( $this->tokens->count() !== 1 ){
      $this->startupsAnalyzedNotSimples();
    }
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
            } else {
              $this->tokens->setValue( 
                $i, $tokenVar->updateVariable(
                  $this->expressionCompare
                )
              );
            }
          }
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
  
  private function startupsClear(
  ): void {
    unset( $this->expressionCompare );
  }  
}