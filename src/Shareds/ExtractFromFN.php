<?php

namespace Websyspro\Entity\Shareds;

use ReflectionFunction;

class ExtractFromFN
{
  public array $tokens;

  public function __construct(
    public mixed $fn
  ){
    $this->startups();
    $this->startupsAnalyzeds();
  }

  private function startups(
  ): void {
    $this->tokens = ExtractToken::get(
      $this->readScripByFunc(
        new ReflectionFunction( $this->fn )
      )
    )->tokens;
  }

  private function rows(
    ReflectionFunction $reflectionFunction  
  ): array {
    return file( $reflectionFunction->getFileName());
  }   

  private function readScripByFunc(
    ReflectionFunction $reflectionFunction
  ): string {
    return implode( " ", array_filter( array_slice(
      $this->rows( $reflectionFunction ), 
        $reflectionFunction->getStartLine() - 1, 
        $reflectionFunction->getEndLine() - $reflectionFunction->getStartLine() + 1
      ), fn( string $row ) => !str_starts_with(trim( $row ), "//" )));
  }

  private function dropUnnecessaryParentheses(
  ): void {
    for($i = 0; $i < count( $this->tokens ); $i++){
      if( is_array( $this->tokens[ $i ])){
        if( $this->tokens[ $i ][ 0 ] === T_DOUBLE_ARROW ){
          
          break;
        }
      }
    }
  }

  private function startupsAnalyzeds(
  ): void {
    $this->dropUnnecessaryParentheses();    
  }

  public static function get(
    callable $fn
  ): ExtractFromFN {
    return new static( $fn );
  }  
}