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
  public array $statements;
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
      if( str_starts_with( $handleFgets, "use " )){
        $this->statements[] = trim( $handleFgets, "\r\n;" );
      }
      if( str_contains( $handleFgets, "class" )){
        break;
      }
    }

    $this->statements = $this->mapper(
      $this->statements, function( string $statement ){
        $statement = str_replace( "use ", "", $statement );
        if( str_contains( $statement, "as" )){
          [ $statement, $alias ] = explode( "as", $statement );
          return [ K_STATEMENTS => $statement, K_VARIABLE => $alias ];
        } else {
          $statementsPaths = explode( "\\", $statement );
          [ $statement, $alias ] = [ $statement, ...$this->slice( $statementsPaths, -1, 1 )];
          return [ K_STATEMENTS => $statement, K_VARIABLE => $alias ];
        }
      }
    );

    return $this->statements;
  }

  public function setSignaryAndUsesStatements(
  ): void {
    if( isset( $this->signary ) === false){
      $this->signary = md5(
        $this->reflectionFunction->getShortName()
      );
    }

    if( isset( $this->statements ) === false ){
      $this->statements = $this->extractUseStatements();
    }
  }

  private function getStatementByVariable(
    array $scope
  ): string|null {
    $statements = $this->filter( 
      $this->statements, fn( array $statement ) => (
        $statement[ K_VARIABLE ] === $scope[T_TOKEN_VALUE]
      )
    );

    if(empty( $statements )){
      return null;
    }

    [ $statement ] = $statements;
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

  private function extractHash(
    array $tokens
  ): string {
    return md5( json_encode( $tokens ));
  }  

  private function extractScopeAndTokens(
    ReflectionFunction &$reflectionFunction
  ): array {
    $handle = new SplFileObject(
      $reflectionFunction->getFileName()
    );

    $handle->seek( $reflectionFunction->getStartLine() - 1);
    while( $handle->eof() === false ){
      $handleFGets = trim( $handle->fgets());
      if( strpos( $handleFGets, "//" ) !== false ){
        $handleFGets = substr( 
          $handleFGets, 0, strpos(
            $handleFGets, "//"
          )
        );
      }

      $tokens[] = $handleFGets;
      if( $handle->key() >= $reflectionFunction->getEndLine() - 1){
        break;
      }
    }

    $tokens = $this->tokenized( $tokens );
    return [ $this->extractScope( $tokens ), $this->extractTokens( $tokens ), $this->extractHash( $tokens )];
  }

  public function where(
    Closure $closure
  ): self {
    $this->reflectionFunction = new ReflectionFunction( $closure );
    if( $this->reflectionFunction instanceof ReflectionFunction ){
      $this->setSignaryAndUsesStatements();
      [ $scope, $tokens, $hash ] = $this->extractScopeAndTokens( $this->reflectionFunction );
      $expresionWhere = new ExpressionWhere( $this->signary, $hash, $this->statements, $scope, $tokens );
      $expresionWhere->analysisLexicalInitial();
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

      $expresionSelect = new ExpressionSelect( $this->signary, $scope, $tokens );
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