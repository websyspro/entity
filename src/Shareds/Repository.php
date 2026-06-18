<?php

namespace Websyspro\Entity\Shareds;

use ReflectionFunction;
use Closure;
use SplFileObject;

define( "K_STATEMENTS", "statements" );
define( "K_VARIABLE", "variable" );

class Repository
extends Utils
{
  public string $signary;
  public array $useStatements;
  public ReflectionFunction $reflectionFunction;
 
  public function __construct(
    public string $class,
  ){}

  public function extractUseStatements(
  ): array {
    $handle = new SplFileObject(
      $this->reflectionFunction->getFileName()
    );

    while( $handle->eof() === false ){
      $handleFgets = $handle->fgets();
      $this->useStatements[] = trim( $handleFgets, "\r\n;" );
      if( str_contains( $handleFgets, "class" )){
        break;
      }
    }
    
    $this->useStatements = $this->filter( 
      $this->useStatements, fn( string $useStatements ) => (
        str_starts_with($useStatements, "use ")
      )
    );

    $this->useStatements = $this->mapper(
      $this->useStatements, function( string $useStatements ){
        $useStatements = str_replace( "use ", "", $useStatements );
        if( str_contains( $useStatements, "as" )){
          [ $statements, $alias ] = explode( "as", $useStatements );
          return [ K_STATEMENTS => $statements, K_VARIABLE => $alias ];
        } else {
          $statementsPaths = explode( "\\", $useStatements );
          [ $statements, $alias ] = [ $useStatements, ...$this->slice( $statementsPaths, -1, 1 )];
          return [ K_STATEMENTS => $statements, K_VARIABLE => $alias ];
        }
      }
    );

    return $this->useStatements;
  }

  public function setSignaryAndUsesStatements(
  ): void {
    if( isset( $this->signary ) === false){
      $this->signary = md5(
        $this->reflectionFunction->getShortName()
      );
    }

    if( isset( $this->useStatements ) === false ){
      $this->useStatements = $this->extractUseStatements();
    }
  }

  public function where(
    Closure $closure
  ): self {
    $this->reflectionFunction = new ReflectionFunction( $closure );
    if( $this->reflectionFunction instanceof ReflectionFunction ){
      $this->setSignaryAndUsesStatements();
    }

    return $this;
  }

  public function select(
    Closure $closure
  ): self {
    $this->reflectionFunction = new ReflectionFunction( $closure );
    if( $this->reflectionFunction instanceof ReflectionFunction ){
      $this->setSignaryAndUsesStatements();
    }
    return $this;
  }
  
  public function orderBy(
    Closure $closure
  ): self {
    $this->reflectionFunction = new ReflectionFunction( $closure );
    if( $this->reflectionFunction instanceof ReflectionFunction ){
      $this->setSignaryAndUsesStatements();
    }
    return $this;
  }  
}