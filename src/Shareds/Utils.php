<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use function array_slice, is_string, is_array, array_map, array_filter, array_values, in_array;

define( 'T_HASH', 'hash' ); 
define( 'T_CONTEXTS', 'contexts' );

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

define( 'T_EXP_INITIAL', 'ExpIntial' );
define( 'T_EXP_DENYING', 'ExpDenying' );
define( 'T_EXP_GROUP', 'ExpGroup' );
define( 'T_EXP_LOGICAL', 'ExpLogical' );
define( 'T_EXP_COMPARE', 'ExpCompare' );
define( 'T_EXP_BETWEEN', 'ExpBetween' );
define( 'T_EXP_ISNULL', 'ExpNull' );
define( 'T_EXP_ISNOTNULL', 'ExpNotNull' );
define( 'T_EXP_LIKE', 'ExpLike' );
define( 'T_EXP_IN', 'ExpIn' );
define( 'T_EXP_UNARY', 'ExpUnary' );
define( 'T_EXP_SUBQUERY', 'ExpSubQuery' );
define( 'T_EXP_FIELD', 'ExpField' );
define( 'T_EXP_EQUAL', 'ExpEqual' );
define( 'T_EXP_VALUE', 'ExpValue' );

define( 'T_Entity', 'entity' );
define( 'T_Columns', 'columns' );
define( 'T_Types', 'types' );
define( 'T_Alias', 'alias' );
define( 'T_Indexes', 'indexes' );
define( 'T_Uniques', 'uniques' );
define( 'T_Foreign_Keys', 'foreign_keys' );
define( 'T_Primary_Keys', 'primary_keys' );
define( 'T_Not_Nulls', 'not_nulls' );
define( 'T_Auto_Increments', 'auto_increments' );

define( 'T_ACTION_TO_ADJUST_SIDE', 1 );
define( 'T_ACTION_TO_ADJUST_EQUALS', 2 );
define( 'T_ACTION_TO_METHODS', 3 );
define( 'T_ACTION_TO_BETWEEN', 4 );
define( 'T_ACTION_TO_LIKE', 5 );
define( 'T_ACTION_TO_IN', 6 );

define( "T_OBJECT", "object" );
define( "T_PARENT", "parent" );
define( "T_CHILDS", "childs" );
define( "T_VALUES", "values" );
define( "T_SCHEME", "scheme" );
define( "T_COLUMN", "column" );
define( "T_COLUMN_TYPE", "columnType" );
define( "T_COLUMN_METHODS", "columnMethods" );
define( "T_COLUMN_METHOD_NAME", "name" );
define( "T_COLUMN_METHOD_TYPE", "type" );
define( "T_COLUMN_METHOD_ARGS", "args" );

define( "T_TOKEN_KEY", "tokenKey" );
define( "T_TOKEN_NAME", "tokenName" );
define( "T_TOKEN_VALUE", "tokenValue" );

class Utils
{
  public function inc(
    int $number
  ): int {
    return ++$number;
  }

  public function dec(
    int $number,
    int $extraDec = 0
  ): int {
    return ( --$number ) - $extraDec;
  }

  public function slice(
    array $items,
    int $offset,
    int|null $length = null
  ): array {
    return array_slice( $items, $offset, $length );
  }  

  public function mapper(
    array $items,
    Closure $closure
  ): array {
    return array_map( $closure, $items, array_keys( $items ));
  }

  public function filter(
    array $items,
    Closure $closure
  ): array {
    return array_values( array_filter( $items, $closure ));
  }

  public function indexOf(
    array $tokens,
    int $type
  ): int {
    foreach( $tokens as $cursor => $token ){
      if( $token[T_TOKEN_KEY] === $type ){
        return $cursor;
      }
    }

    return -1;
  }

  public function groupByTypes(
    array $type,
    array $tokens,
     bool $showKey = false,
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if( in_array( $token[T_TOKEN_KEY], $type ) && $depth === 0){
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

      if($token[T_TOKEN_KEY] === T_START_PARENTESES) $depth++;
      if($token[T_TOKEN_KEY] === T_END_PARENTESES) $depth--;
    }

    if( $curr ){
      $accu[] = $curr;
    }

    return $accu;
  }

  public function groupByTypesLogical(
    array $contexts = []
  ): array {
    return $this->groupByTypes([ 
      T_LOGICAL_AND, T_LOGICAL_OR, T_BOOLEAN_AND, T_BOOLEAN_OR 
    ], $contexts, true );
  }  

  public function contextsNotEnds(
    array $contexts,
    int $parenteses = 0
  ): array {
    for($i=0; $i<count($contexts); $i++){
      if($contexts[$i][T_TOKEN_KEY] === T_START_PARENTESES){
        $parenteses++;
      }

      if($contexts[$i][T_TOKEN_KEY] === T_END_PARENTESES){
        $parenteses--;

        if($parenteses < 0){
          $contexts = array_slice(
            $contexts, 0, $i
          ); break;
        }          
      }

      if($parenteses < 1){
        if($contexts[$i][T_TOKEN_KEY] === T_SEMICOLON){
          $contexts = array_slice(
            $contexts, 0, $i
          ); break;
        }
      }
    };

    return $contexts;
  }  

  public function namberToken(
    int $namberToken
  ): string {
    return match( $namberToken ){
       34 => 'T_ASP',
       40 => 'T_START_PARENTESES',
       41 => 'T_END_PARENTESES',
       91 => 'T_START_BRACKET',
       93 => 'T_END_BRACKET',
       46 => 'T_DOT',
       44 => 'T_COMMA',
       59 => 'T_SEMICOLON',
       58 => 'T_COLON',
       63 => 'T_QUESTION',
       43 => 'T_PLUS',
       45 => 'T_MINUS',
       42 => 'T_MULTIPLY',
       47 => 'T_DIVIDE',
       61 => 'T_EQUAL',
       62 => 'T_GREATER_THAN',
       60 => 'T_LESS_THAN',
       33 => 'T_NOT',
      123 => 'T_START_BRACE',
      125 => 'T_END_BRACE',
        default => token_name( $namberToken )
    };
  }  

  public function createToken(
    string|array $tokenArgs
  ): array {
    [ $tokenKey, $tokenValue ] = is_string( $tokenArgs ) 
      ? [ ord( $tokenArgs ), $tokenArgs ] : $tokenArgs;

    if( in_array( $tokenKey, [ T_CONSTANT_ENCAPSED_STRING ])){
      $tokenValue = trim( $tokenValue, '"\'' );
    }  

    return [
      T_TOKEN_KEY => $tokenKey,
      T_TOKEN_VALUE => $tokenValue, 
      T_TOKEN_NAME => $this->namberToken(
        $tokenKey
      )
    ];
  }  
  
  public function tokenized(
    array $codeArr
  ): array {
    $tokens = array_slice(
      token_get_all( sprintf(
        "<?php %s", implode( "", $codeArr )
      )), 1 , null, true
    );

    $tokens = $this->filter( $tokens,
      fn( array|string $token ) => (
        is_string( $token ) || is_array( $token ) && $token[0] !== T_WHITESPACE
      )
    );

    $tokens = $this->mapper(
      $tokens, fn( array|string $token ) => (
        $this->createToken( $token )
      )
    );

    return $this->contextsNotEnds( $tokens );
  }

  public function isDenying(
    array $tokens = []
  ): bool {
    return $tokens[0][T_TOKEN_KEY] === T_NOT;
  }
  
  public function isGroup(
    array $tokens = []
  ): bool {
    return $tokens[0][T_TOKEN_KEY] === T_START_PARENTESES;
  }
  
  public function getSubQueryMethod(
    array $tokens = []    
  ): array|null {
    $subQueryMethod = array_slice(
      $tokens, $this->dec(
        $this->indexOf( $tokens, T_FN ), 1
      ), 1
    );

    return $subQueryMethod ?? null;
  }  

  public function isSubQuery(
    array $contexts = []    
  ): bool {
    [ $subQueryMethod ] = $this->getSubQueryMethod($contexts);
    return in_array( $subQueryMethod[T_TOKEN_VALUE], [ 'any' ]);
  }
  
  public function isLogical(
    array $contexts = []
  ): bool {
    return in_array( $contexts[0][T_TOKEN_KEY], [
      T_LOGICAL_AND, T_LOGICAL_OR, T_BOOLEAN_AND, T_BOOLEAN_OR 
    ]);
  }
  
  public function isCompare(
    array $contexts = []
  ): bool {
    return empty(
      $this->filter( $contexts, fn(array $token) => in_array( $token[T_TOKEN_KEY], [
        T_IS_NOT_IDENTICAL, T_IS_GREATER_OR_EQUAL, T_IS_SMALLER_OR_EQUAL,
        T_EQUAL, T_IS_EQUAL, T_IS_IDENTICAL, T_IS_NOT_EQUAL,
        T_GREATER_THAN, T_LESS_THAN
      ]))
    ) ? false : true;
  }

  public function isUnary(
    array $tokens = []
  ): bool {
    return $this->isCompare($tokens) === false;
  }  

  public function getExpType(
    array $contexts = []
  ): string|null {
    if( $this->isDenying( $contexts )){
      return T_EXP_DENYING;
    } else if( $this->isGroup( $contexts )){
      return T_EXP_GROUP;
    } else if( $this->isSubQuery( $contexts )){
      return T_EXP_SUBQUERY;
    } else if( $this->isLogical( $contexts )){
      return T_EXP_LOGICAL;
    } else if( $this->isCompare( $contexts )){
      return T_EXP_COMPARE;
    } else if( $this->isUnary( $contexts )){
      return T_EXP_UNARY;
    }
      
    return null;
  }  
}