<?php

namespace Websyspro\Entity\Shareds;

use function ord, count, is_string, array_slice, sprintf;
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
define("T_EXPRESSION_UNARY", "ExpressionUnary");
define("T_EXPRESSION_GROUP", "ExpressionGroup");
define("T_EXPRESSION_LOGICAL", "ExpressionLogical");
define("T_EXPRESSION_SUBQUERY", "ExpressionSubQuery");
define("T_EXPRESSION_COMPARE", "ExpressionCompare");

class ExpressionUtil
{
  public function find(
    array $tokens,
    int $number
  ): int {
    foreach($tokens as $key => $token){
      if(isset($token["number" ])){
        if($token["number"] === $number){
          return $key;
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

  public function tokensAll(
    Closure $closure,
    array $tokens = []
  ): array {
    if(is_callable($closure)){
      $tokens = $this->tokensByReflection(
        new ReflectionFunction(
          $closure
        )
      );

      for($i=0; $i < count($tokens); $i++){
        $tokens[$i] = is_array($tokens[$i])
          ? $this->createToken($tokens[$i]) 
          : $this->createToken($tokens[$i]);
      }      
    }

    $tokens = $this->dropWriteSpace($tokens);
    $tokens = $this->dropInitialInvalids($tokens);
    $tokens = $this->dropEndInvalids($tokens);
    return $this->createClosure($tokens);
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

    return [ "number" => $number, "value" => $value, "type" => $this->namberToken( $number )];
  }

  public function dropWriteSpace(
    array $tokens
  ): array {
    for( $i=0; $i<count( $tokens ); $i++ ){
      if( $tokens[ $i ][ "number" ] === T_WHITESPACE ){
        array_splice( $tokens, $i, 1 ); $i--;
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
      if($tokens[$i]["number"] === T_START_PARENTESES){
        $parenteses++;
      }

      if($tokens[$i]["number"] === T_END_PARENTESES){
        $parenteses--;

        if($parenteses < 0){
          $tokens = array_slice(
            $tokens, 0, $i
          ); break;
        }          
      }

      if($parenteses < 1){
        if($tokens[$i]["number"] === T_SEMICOLON){
          $tokens = array_slice(
            $tokens, 0, $i
          ); break;
        }
      }
    };

    return $tokens;
  }

  public function isLogical(
    int $number
  ): bool {
    return $number === T_LOGICAL_AND
        || $number === T_LOGICAL_OR
        || $number === T_BOOLEAN_AND
        || $number === T_BOOLEAN_OR;
  }

  public function parserTokens(
    array $expressionNode = [],
    array $tokensCurrent = [],
    array $tokensAccumulate = [],
      int $tokensDepth = 0
  ): array {
    for($i=0; $i<count($expressionNode); $i++){
      if($this->isLogical($expressionNode[$i]["number"]) && $tokensDepth===0){
        if($tokensCurrent){
          $tokensAccumulate[] = $tokensCurrent;
          $tokensCurrent = [];
        }

        $tokensAccumulate[] = $expressionNode[$i];
        continue;
      }

      $tokensCurrent[] = $expressionNode[$i];

      if($expressionNode[$i]["number"] === T_START_PARENTESES) $tokensDepth++;
      if($expressionNode[$i]["number"] === T_END_PARENTESES) $tokensDepth--;
    }

    if($tokensCurrent){
      $tokensAccumulate[] = $tokensCurrent;
    }

    return $tokensAccumulate;
  }

  public function createScopes(
    array $tokens
  ): array {
    $scopes = array_chunk(
      array_slice($tokens, 
        $this->findNext( $tokens, T_START_PARENTESES),
        $this->findPrev( $tokens, T_END_PARENTESES) - 1
      ), 2
    );

    return array_map(
      function(array $scope){
        [ $instance, $variable ] = $scope;
        return [ "instance" => $instance[ "value" ], "variable" => $variable[ "value" ]];
      }, $scopes
    );
  }

  public function createExpressionNode(
    array $tokens
  ): array {
    return $this->parserTokens(
      array_slice($tokens, $this->findNext($tokens, T_DOUBLE_ARROW))
    );
  }

  public function createClosure(
    array $tokens
  ): array {
    return [
      "scopes" => $this->createScopes($tokens),
      "tokens" => $this->createExpressionNode($tokens)
    ];
  }
}