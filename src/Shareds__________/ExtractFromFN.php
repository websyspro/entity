<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use ReflectionFunction;
use Closure;

class ExtractFromFN
{
  public Collection $tokens;

  public function __construct(
    public Closure $closure
  ){
    $this->startups();
  }

  private function startups(
  ): void {
    $this->tokens = ExtractToken::get(
      $this->readScripByFunc(
        new ReflectionFunction( 
          $this->closure
        )
      )
    )->tokens;
  }

  private function rows(
    ReflectionFunction $reflectionFunction  
  ): Collection {
    return new Collection( file( $reflectionFunction->getFileName()));
  }   

  private function readScripByFunc(
    ReflectionFunction $reflectionFunction
  ): string {
    return $this->rows( $reflectionFunction )->slice(
      $reflectionFunction->getStartLine() - 1, 
      $reflectionFunction->getEndLine() - $reflectionFunction->getStartLine() + 1
    )->where( fn( string $row ) => !str_starts_with( trim( $row ), "//" ))->joinWithSpace();
  }
  
  public static function get(
    Closure $closure
  ): ExtractFromFN {
    return new static( $closure );
  }  
}