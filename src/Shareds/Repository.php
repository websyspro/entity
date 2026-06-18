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

  private function getStatementByVariable(
    array $statements
  ): string|null {
    $statement = $this->filter( 
      $this->useStatements, fn( array $useStatement ) => (
        $useStatement[ K_VARIABLE ] === $statements[T_TOKEN_VALUE]
      )
    );

    if(empty($statement)){
      return null;
    }

    [ $statement ] = $statement;
    return $statement[ K_STATEMENTS ];
  }

  private function extractScope(
    array $tokens
  ): array {
    $tokens = $this->slice(
      $this->slice( $tokens, $this->inc( $this->indexOf( $tokens, T_START_PARENTESES ))), 
        0, $this->dec( $this->indexOf( $tokens, T_END_PARENTESES ), 1 )
    );

    $tokens = $this->groupByTypes(
      [ T_COMMA ], $tokens, true
    );

    return $this->mapper(
      $tokens, function( array $scope ){
        [ $statements, $variables ] = $scope;
        $statements = $this->getStatementByVariable( $statements );
        return [ K_STATEMENTS => $statements, K_VARIABLE => $variables[T_TOKEN_VALUE] ];
      }
    );
  }

  private function extractTokens(
    array $tokens
  ): array {
    return $this->slice( 
      $tokens, $this->inc(
        $this->indexOf( $tokens, T_DOUBLE_ARROW )
      )
    );
  }

  private function extractScopeAndTokens(
    ReflectionFunction &$reflectionFunction
  ): array {
    $handle = new SplFileObject(
      $reflectionFunction->getFileName()
    );

    $handle->seek( $reflectionFunction->getStartLine() - 1);
    while( $handle->eof() === false ){
      $tokens[] = trim( $handle->fgets(), "\r\n" );
      if( $handle->key() >= $reflectionFunction->getEndLine() - 1){
        break;
      }
    }

    $tokens = $this->tokenized( $tokens );
    return [ $this->extractScope( $tokens ), $this->extractTokens( $tokens ) ];
  }

  public function where(
    Closure $closure
  ): self {
    $this->reflectionFunction = new ReflectionFunction( $closure );
    if( $this->reflectionFunction instanceof ReflectionFunction ){
      $this->setSignaryAndUsesStatements();
      [ $scope, $tokens ] = $this->extractScopeAndTokens(
        $this->reflectionFunction
      );

      $expresionWhere = new ExpressionWhere(
        $this->signary, $scope, $tokens
      );
    }

    return $this;
  }

  public function select(
    Closure $closure
  ): self {
    $this->reflectionFunction = new ReflectionFunction( $closure );
    if( $this->reflectionFunction instanceof ReflectionFunction ){
      $this->setSignaryAndUsesStatements();
      [ $scope, $tokens ] = $this->extractScopeAndTokens(
        $this->reflectionFunction
      );

      $expresionSelect = new ExpressionSelect (
        $this->signary, $scope, $tokens
      );

      print_r( $tokens );
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