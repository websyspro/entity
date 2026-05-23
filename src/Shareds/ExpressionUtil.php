<?php

namespace Websyspro\Entity\Shareds;

use function ord, count, is_string, is_array, in_array, array_slice, sprintf;
use ReflectionFunction;
use Closure;

define("T_START_PARENTESES", 40);
define("T_END_PARENTESES", 41);
define("T_START_BRACKET", 91);
define("T_END_BRACKET", 93);
define("T_START_BRACE", 123);
define("T_END_BRACE", 125);
define("T_DOT", 46);
define("T_COMMA", 44);
define("T_SEMICOLON", 59);
define("T_COLON", 58);
define("T_QUESTION", 63);
define("T_PLUS", 43);
define("T_MINUS", 45);
define("T_MULTIPLY", 42);
define("T_DIVIDE", 47);
define("T_EQUAL", 61);
define("T_GREATER_THAN", 62);
define("T_LESS_THAN", 60);
define("T_NOT", 33);

define("T_EXPRESSION_NODE", "ExpressionNode");
define("T_EXPRESSION_GROUP", "ExpressionGroup");
define("T_EXPRESSION_UNARY", "ExpressionUnary");
define("T_EXPRESSION_LOGICAL", "ExpressionLogical");
define("T_EXPRESSION_SUBQUERY", "ExpressionSubQuery");
define("T_EXPRESSION_COMPARE", "ExpressionCompare");
define("T_EXPRESSION_FIELD", "ExpressionField");
define("T_EXPRESSION_VALUE", "ExpressionValue");
define("T_EXPRESSION_EQUAL", "ExpressionEqual");
define("T_EXPRESSION_NEGATIVE", "ExpressionNegative");

define("T_EVENTS_LIST", [ "any" ]);

class ExpressionUtil
{
  public function find(
    array $tokens,
    int $number
  ): int {
    foreach( $tokens as $key => $token ){
      if( isset( $token[ 'number' ])){
        if( (int)$token[ 'number' ] === $number ){
          return (int)$key;
        }        
      }
    }

    return -1;
  }

  public function findNext(
    array $tokens,
    int $number
  ): int {
    return $this->find($tokens, $number) + 1;
  }

  public function findPrev(
    array $tokens,
    int $number
  ): int {
    return $this->find($tokens, $number) - 1;
  }

  public function findFn(
    array $tokens
  ): int {
    return $this->find( $tokens, T_FN ) !== -1;
  }
  
  public function findEguals(
    array $tokens
  ): int {
    foreach( $tokens as $key => $token ){
      if( $token['number'] === T_EQUAL ) return $key;
      if( $token['number'] === T_IS_EQUAL ) return $key;
      if( $token['number'] === T_IS_IDENTICAL ) return $key;
      if( $token['number'] === T_IS_NOT_EQUAL ) return $key;
      if( $token['number'] === T_IS_NOT_IDENTICAL ) return $key;
      if( $token['number'] === T_IS_GREATER_OR_EQUAL ) return $key;
      if( $token['number'] === T_IS_SMALLER_OR_EQUAL ) return $key;
      if( $token['number'] === T_GREATER_THAN ) return $key;
      if( $token['number'] === T_LESS_THAN ) return $key;
    }

    return -1;
  }  

  public function startParentese(
    array $token
  ): bool {
    if(isset($token['number']) === false){
      return false;
    }

    return $token['number'] === T_START_PARENTESES;
  }

  public function endParentese(
    array $token
  ): bool {
    if( isset( $token['number']) === false){
      return false;
    }

    return $token['number'] === T_END_PARENTESES;
  } 

  public function findNegative(
    array $token
  ): int {
    if( isset( $token['number']) === false){
      return false;
    }

    return $token['number'] === T_NOT;
  }  

  public function getScopesByTokens(
    array $tokens
  ): array {
    return array_slice( $tokens, 0, $this->find( $tokens, T_DOUBLE_ARROW ));
  }

  public function getContextByTokens(
    array $tokens
  ): array {
    return array_slice( $tokens, $this->findNext( $tokens, T_DOUBLE_ARROW ));
  }

  public function isExpressionNegative(
    array $tokens
  ): bool {
    [ $token ] = $tokens;
    return $this->findNegative( $token );
  }  

  public function isExpressionGroup(
    array $tokens
  ): bool {
    [ $token ] = $tokens;
    return $this->startParentese( $token );
  }

  public function getEventBySubQuery(
    array $tokens
  ): string {
    [ $tokens ] = array_slice( $tokens, $this->findPrev( $tokens, T_FN ) - 1, 1);
    return $tokens[ 'value' ];
  }

  private function existsEvent(
    array $tokens
  ): bool {
    return in_array( $this->getEventBySubQuery( $tokens ), T_EVENTS_LIST );
  }

  public function isExpressionSubQuery(
    array $tokens
  ): bool {
    return $this->findFn( $tokens )
        && $this->existsEvent( $tokens );
  }

  public function isExpressionLogical(
    array $tokens
  ): bool {
    [ $token ] = $tokens;
    return $this->isLogical( $token );
  }

  public function isExpressionUnary(
    array $tokens
  ): bool {
    return $this->findEguals( $tokens ) === -1;
  }
  
  public function isExpressionCompare(
    array $tokens
  ): bool {
    return $this->findEguals( $tokens ) !== -1;
  } 
  
  public function isExpressionField(
    array $tokens
  ): bool {
    if( count( $tokens ) < 3 ){
      return false;
    }

    [ $variable, $operator, $variableName ] = $tokens;
    return $variable[ 'number' ] === T_VARIABLE 
        && $operator[ 'number' ] === T_OBJECT_OPERATOR 
        && $variableName[ 'number' ] === T_STRING;
  }

  public function isExpressionEquals(
    array $tokens
  ): bool {
    [ $token ] = $tokens;
    return $this->isEquals( $token ) === true;
  }   

  public function isExpressionValue(
    array $tokens
  ): bool {
    return $this->isExpressionField( $tokens ) === false;
  }  

  public function tokensByReflection(
    ReflectionFunction $reflectionFunction
  ): array {
    return array_slice( 
      token_get_all( sprintf( "<?php %s", implode( " ", array_filter(
        array_slice( file( $reflectionFunction->getFileName()), 
          $reflectionFunction->getStartLine() - 1,
          $reflectionFunction->getEndLine() - 
          $reflectionFunction->getStartLine() + 1
        ), fn( string $closureLine ) => !str_starts_with( trim( $closureLine ), "//" )
      )))), 1 
    );
  }

  public function namberToken(
    int $namberToken
  ): string {
    return match( $namberToken ){
      40 => "T_START_PARENTESES",
      41 => "T_END_PARENTESES",
      91 => "T_START_BRACKET",
      93 => "T_END_BRACKET",
      46 => "T_DOT",
      44 => "T_COMMA",
      59 => "T_SEMICOLON",
      58 => "T_COLON",
      63 => "T_QUESTION",
      43 => "T_PLUS",
      45 => "T_MINUS",
      42 => "T_MULTIPLY",
      47 => "T_DIVIDE",
      61 => "T_EQUAL",
      62 => "T_GREATER_THAN",
      60 => "T_LESS_THAN",
      33 => "T_NOT",
      123 => "T_START_BRACE",
      125 => "T_END_BRACE",
        default => token_name( $namberToken )
    };
  }

  public function createToken(
    array|string $tokenArgs
  ): array {
    [ $number, $value ] = is_string( $tokenArgs ) 
      ? [ ord( $tokenArgs ), $tokenArgs ] 
      : $tokenArgs;

    return [
      'number' => $number,
      'value' => $value,
      "type" => $this->namberToken( $number )
    ];
  }

  public function dropWriteSpace(
    array $tokens
  ): array {
    for($i=0; $i<count($tokens); $i++){
      if($tokens[$i]['number'] === T_WHITESPACE){
        array_splice($tokens, $i, 1); $i--;
      } 
    }
    
    return $tokens;
  }

  public function dropInitialInvalids(
    array $tokens
  ): array {
    return array_slice($tokens, $this->find($tokens, T_FN));
  }

  public function dropEndInvalids(
    array $tokens,
    int $parenteses = 0
  ): array {
    for($i=0; $i<count($tokens); $i++){
      if($tokens[$i]['number'] === T_START_PARENTESES){
        $parenteses++;
      }

      if($tokens[$i]['number'] === T_END_PARENTESES){
        $parenteses--;

        if($parenteses < 0){
          $tokens = array_slice(
            $tokens, 0, $i
          ); break;
        }          
      }

      if($parenteses < 1){
        if($tokens[$i]['number'] === T_SEMICOLON){
          $tokens = array_slice(
            $tokens, 0, $i
          ); break;
        }
      }
    };

    return $tokens;
  }

  public function dropParenteses(
    array $tokens, 
    int $i = 0
  ): array {
    while( $i < count( $tokens )){
      if( $tokens[$i]['number'] === T_START_PARENTESES ){
        array_splice( $tokens, count($tokens) - 1, 1 );
        array_splice( $tokens, $i, 1 ); 
        $i--;
      }
      
      break;
    }
    return $tokens;
  }  

  public function dropNegative(
    array $tokens
  ): array {
    return array_slice( $tokens, 1);
  }  
  
  public function tokensAll(
    Closure $closure,
    array $tokens = []
  ): array {
    if( is_callable( $closure )){
      $tokens = $this->tokensByReflection(
        ClosureUtil::getReflectFunction(
          $closure
        )
      );

      for($i=0; $i < count($tokens); $i++){
        $tokens[$i] = is_array( $tokens[ $i ]) 
          ? $this->createToken( $tokens[ $i ]) 
          : $this->createToken( $tokens[ $i ]);
      }      
    }

    $tokens = $this->dropWriteSpace($tokens);
    $tokens = $this->dropInitialInvalids($tokens);
    $tokens = $this->dropEndInvalids($tokens);
    return $tokens;
  }  

  public function isLogical(
    array $token
  ): bool {
    if(isset($token['number']) === false){
      return false;
    }

    return $token['number'] === T_LOGICAL_AND
        || $token['number'] === T_LOGICAL_OR
        || $token['number'] === T_BOOLEAN_AND
        || $token['number'] === T_BOOLEAN_OR;
  }

  public function parserTokens(
    array $tokens = [],
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if( $this->isLogical( $token ) && $depth === 0){
        if( $curr ){
          $accu[] = $curr;
          $curr = [];
        }

        $accu[] = [ $token ];
        continue;
      }

      $curr[] = $token;

      if( $this->startParentese( $token )) $depth++;
      if( $this->endParentese( $token )) $depth--;
    }

    if( $curr ){
      $accu[] = $curr;
    }

    return $accu;
  }

  public function isEquals(
    array $token
  ): bool {
    if( isset( $token[ 'number' ]) === false){
      return false;
    }

    return $token[ 'number' ] === T_EQUAL
        || $token[ 'number' ] === T_IS_EQUAL
        || $token[ 'number' ] === T_IS_IDENTICAL
        || $token[ 'number' ] === T_IS_NOT_EQUAL
        || $token[ 'number' ] === T_IS_NOT_IDENTICAL
        || $token[ 'number' ] === T_IS_GREATER_OR_EQUAL
        || $token[ 'number' ] === T_IS_SMALLER_OR_EQUAL
        || $token[ 'number' ] === T_GREATER_THAN
        || $token[ 'number' ] === T_LESS_THAN;
  }
  
  public function parserTokensCompare(
    array $tokens = [],
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if( $this->isEquals( $token ) && $depth === 0){
        if( $curr ){
          $accu[] = $curr;
          $curr = [];
        }

        $accu[] = [ $token ];
        continue;
      }

      $curr[] = $token;

      if( $this->startParentese( $token )) $depth++;
      if( $this->endParentese( $token )) $depth--;
    }

    if( $curr ){
      $accu[] = $curr;
    }

    return $accu;
  }
  
  public function getInstance(
    string $variable,
    array $scopes
  ): string {
    [ $scope ] = array_values( array_filter( $scopes,
      fn( array $scope ) => $scope[ "variable" ] === $variable
    ));

    return $scope[ "instance" ];
  }

  public function getScopes(
    Closure|int $closure,
    array $tokens,
    array $scopesParents = [],
  ): array {
    $scopes = array_chunk(
      array_slice( $tokens, 
        $this->findNext( $tokens, T_START_PARENTESES ),
        $this->findPrev( $tokens, T_END_PARENTESES ) - 1
      ), 2
    );

    $scopes = array_map(
      function(array $scope) use( $closure ){
        [ $instance, $variable ] = $scope;
        return [
          "instance" => ClosureUtil::getUse( $closure, $instance[ "value" ]), 
          "variable" => $variable[ "value" ]
        ];
      }, $scopes
    );

    return array_merge( $scopesParents, $scopes );
  }

  public function getContext(
    array $tokens
  ): array {
    return array_slice( $tokens, $this->findNext( $tokens, T_DOUBLE_ARROW ));
  }
}