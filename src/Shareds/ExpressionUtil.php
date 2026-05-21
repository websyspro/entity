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
define("T_EXPRESSION_LOGICAL", "ExpressionLogical");
define("T_EXPRESSION_SUBQUERY", "ExpressionSubQuery");
define("T_EXPRESSION_COMPARE", "ExpressionCompare");
define("T_EXPRESSION_NEGATIVE", "ExpressionNegative");

define("T_KEY_OBJECT", "object");
define("T_KEY_SCOPES", "scopes");
define("T_KEY_TOKENS", "tokens");
define("T_KEY_CLOSURE", "closure");

define("T_KEY_TOKEN_NAMBER", "number");
define("T_KEY_TOKEN_VALUE", "value");

define("T_EVENTS_LIST", [ "any" ]);

class ExpressionUtil
{
  public function find(
    array $tokens,
    int $number
  ): int {
    foreach($tokens as $key => $token){
      if(isset($token[T_KEY_TOKEN_NAMBER])){
        if($token[T_KEY_TOKEN_NAMBER] === $number){
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

  public function findFn(
    array $tokens
  ): int {
    return $this->find( $tokens, T_FN ) !== -1;
  }  

  public function startParentese(
    array $token
  ): bool {
    if(isset($token[T_KEY_TOKEN_NAMBER]) === false){
      return false;
    }

    return $token[T_KEY_TOKEN_NAMBER] === T_START_PARENTESES;
  }

  public function endParentese(
    array $token
  ): bool {
    if( isset( $token[ T_KEY_TOKEN_NAMBER ]) === false){
      return false;
    }

    return $token[ T_KEY_TOKEN_NAMBER ] === T_END_PARENTESES;
  } 

  public function findNegative(
    array $token
  ): int {
    if( isset( $token[ T_KEY_TOKEN_NAMBER ]) === false){
      return false;
    }

    return $token[ T_KEY_TOKEN_NAMBER ] === T_NOT;
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

  private function existsEvent(
    array $tokens
  ): bool {
    [ $tokens ] = array_slice( $tokens, $this->findPrev( $tokens, T_FN ) - 1, 1);
    return in_array( $tokens[ T_KEY_TOKEN_VALUE ], T_EVENTS_LIST );
  }

  public function isExpressionSubQuery(
    array $tokens
  ): bool {
    return $this->findFn( $tokens )
        && $this->existsEvent( $tokens );
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
      T_KEY_TOKEN_NAMBER => $number,
      T_KEY_TOKEN_VALUE => $value,
      "type" => $this->namberToken( $number )
    ];
  }

  public function dropWriteSpace(
    array $tokens
  ): array {
    for($i=0; $i<count($tokens); $i++){
      if($tokens[$i][T_KEY_TOKEN_NAMBER] === T_WHITESPACE){
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
      if($tokens[$i][T_KEY_TOKEN_NAMBER] === T_START_PARENTESES){
        $parenteses++;
      }

      if($tokens[$i][T_KEY_TOKEN_NAMBER] === T_END_PARENTESES){
        $parenteses--;

        if($parenteses < 0){
          $tokens = array_slice(
            $tokens, 0, $i
          ); break;
        }          
      }

      if($parenteses < 1){
        if($tokens[$i][T_KEY_TOKEN_NAMBER] === T_SEMICOLON){
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
      if( $tokens[ $i ][ T_KEY_TOKEN_NAMBER ] === T_START_PARENTESES ){
        array_splice( $tokens, count($tokens) - 1, 1 );
        array_splice( $tokens, $i, 1 ); 
        $i--;
      }
      
      break;
    }
    return $tokens;
  }  

  public function dropParentesesInitialExtras(
    array $tokens, 
    int $i = 0
  ): array {
    $scopes = $this->getScopesByTokens($tokens);
    $contexts = $this->getContextByTokens($tokens);
    $contexts = $this->dropParenteses($contexts);

    return array_merge( 
      $scopes, [
        $tokens[ $this->find( $tokens, T_DOUBLE_ARROW )]
      ], $contexts
    );
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
        new ReflectionFunction( $closure)
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
    $tokens = $this->dropParentesesInitialExtras($tokens);
    return $tokens;
  }  

  public function isLogical(
    array $token
  ): bool {
    if(isset($token[T_KEY_TOKEN_NAMBER]) === false){
      return false;
    }

    return $token[T_KEY_TOKEN_NAMBER] === T_LOGICAL_AND
        || $token[T_KEY_TOKEN_NAMBER] === T_LOGICAL_OR
        || $token[T_KEY_TOKEN_NAMBER] === T_BOOLEAN_AND
        || $token[T_KEY_TOKEN_NAMBER] === T_BOOLEAN_OR;
  }

  public function parserTokens(
    array $tokens = [],
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if($this->isLogical($token) && $depth === 0){
        if( $curr ){
          $accu[] = $curr;
          $curr = [];
        }

        $accu[] = [ $token ];
        continue;
      }

      $curr[] = $token;

      if($this->startParentese($token)) $depth++;
      if($this->endParentese($token)) $depth--;
    }

    if( $curr ){
      $accu[] = $curr;
    }

    return $accu;
  }

  public function getClosureId(
    Closure $closure
  ): int {
    return spl_object_id( $closure );
  }

  public function getScopes(
    array $tokens
  ): array {
    $scopes = array_chunk(
      array_slice( $tokens, 
        $this->findNext( $tokens, T_START_PARENTESES ),
        $this->findPrev( $tokens, T_END_PARENTESES ) - 1
      ), 2
    );

    return array_map(
      function(array $scope){
        [ $instance, $variable ] = $scope;
        return [ "instance" => $instance[ "value" ], "variable" => $variable[ "value" ]];
      }, $scopes
    );
  }

  public function getContext(
    array $tokens
  ): array {
    return array_slice( $tokens, $this->findNext( $tokens, T_DOUBLE_ARROW ));
  }
}