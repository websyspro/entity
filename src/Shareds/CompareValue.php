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
    } else {
    //   var_dump( "quero dizer que VALUE é simples:" );
    //   print_r( $this->tokens );
    }
  }

  private function isEnumValue(
  ): bool {
    return false;
  }

  private function isVariable(
  ): bool {
    return false;
  }  

  private function startupsAnalyzedNotSimples(
  ): void {
    print_r( $this->tokens );
  }
  
  private function startupsClear(
  ): void {
    unset( $this->expressionCompare );
  }  
}