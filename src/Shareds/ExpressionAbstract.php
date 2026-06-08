<?php

namespace Websyspro\Entity\Shareds;

use function in_array, count;
use ReflectionFunction;
use Closure;

/* defined consts to tokens */
define( 'T_START_PARENTESES', 40 );
define( 'T_END_PARENTESES', 41 );
define( 'T_START_BRACKET', 91 );
define( 'T_END_BRACKET', 93 );
define( 'T_START_BRACE', 123 );
define( 'T_END_BRACE', 125 );
define( 'T_DOT', 46 );
define( 'T_COMMA', 44 );
define( 'T_SEMICOLON', 59 );
define( 'T_COLON', 58 );
define( 'T_QUESTION', 63 );
define( 'T_PLUS', 43 );
define( 'T_MINUS', 45 );
define( 'T_MULTIPLY', 42 );
define( 'T_DIVIDE', 47 );
define( 'T_EQUAL', 61 );
define( 'T_GREATER_THAN', 62 );
define( 'T_LESS_THAN', 60 );
define( 'T_NOT', 33 );

/* defined consts to objects */
define( 'T_EXP_INITIAL', 'ExpIntial' );
define( 'T_EXP_DENYING', 'ExpDenying' );
define( 'T_EXP_GROUP', 'ExpGroup' );
define( 'T_EXP_LOGICAL', 'ExpLogical' );
define( 'T_EXP_COMPARE', 'ExpCompare' );
define( 'T_EXP_BETWEEN', 'ExpBetween' );
define( 'T_EXP_UNARY', 'ExpUnary' );
define( 'T_EXP_SUBQUERY', 'ExpSubQuery' );
define( 'T_EXP_FIELD', 'ExpField' );
define( 'T_EXP_EQUAL', 'ExpEqual' );
define( 'T_EXP_VALUE', 'ExpValue' );

class ExpressionAbstract
{
  public ReflectionFunction $reflectionFunction;
  public array $tokens = [];
  public array $uses = [];

  public function __construct(
    public Closure $closure
  ){}

  public function inc(
    int $number
  ): int {
    return ++$number;
  }

  public function dec(
    int $number,
    int $decNumner = 0
  ): int {
    return (--$number) - $decNumner;
  }

  public function indexOf(
    array $tokens,
    int $type
  ): int {
    foreach( $tokens as $cursor => $token ){
      if( $token[0] === $type ){
        return $cursor;
      }
    }

    return -1;
  }

  public function groupByTypes(
    array $tokens,
    array $type,
     bool $showKey = false,
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if( in_array( $token[0], $type ) && $depth === 0){
        if( $curr ){
          $accu[] = $curr;
          $curr = [];
        } 
        
        if( $showKey ){
          $accu[] = [ $token ];
        }

        continue;
      }

      $curr[] = $token;

      if($token[0] === T_START_PARENTESES) $depth++;
      if($token[0] === T_END_PARENTESES) $depth--;
    }

    if( $curr ){
      $accu[] = $curr;
    }

    return $accu;
  }

  public function getFileRows(
  ): void {
    $this->reflectionFunction = new ReflectionFunction($this->closure);
    if( $this->reflectionFunction instanceof ReflectionFunction ){
      $this->tokens = file( $this->reflectionFunction->getFileName());

      if( count( $this->tokens ) !== 0 ){
        $this->tokens = array_map(
          function(string $token){
            $strPos = strpos($token, '//');
            return $strPos 
              ? substr($token, 0, $strPos)
              : $token;
          }, $this->tokens 
        );
      }
    }
  }

  public function getUsesRows(
  ): void {
    $this->uses = array_values(
      array_filter(
        $this->tokens, fn(string $token) => (
          str_starts_with( trim( $token), 'use')
        )
      )
    );

    $this->uses = array_map( 
      fn(string $token) => (
        str_replace([ 'use',';' ], '', $token)
      ), $this->uses
    );

    $this->uses = array_map(
      function(string $token){
        if( strpos($token, 'as') !== false ){
          [ $use, $key ] = explode( 'as', $token );
          return [ trim($use), trim($key)];
        } else {
          $useImplits = explode('\\', $token);
          return [
            trim( implode( '\\', array_slice($useImplits, 0))), 
            trim( implode( '\\', array_slice($useImplits, -1)))
          ];
        }
      }, $this->uses
    );
  }

  public function getUse(
    string $variable
  ): string|null {
    [ $uses ] = array_values(
      array_filter(
        $this->uses, fn(array $use) => $use[1] === $variable
      )
    );

    return $uses[0] ?? null;
  }  
}