<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use ReflectionFunction;

use function ord, array_slice, in_array, sprintf, is_string, count;

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

class TokensByClosure
{
  private ReflectionFunction $reflectionFunction;
  private string|array $tokens;
  private array $scopes;
  private array $rows;
  private string $classe;
  private string $method;

  public function __construct(
    public Closure $closure
  ){}

  private function defineReflectFunction(
  ): void {
    $this->reflectionFunction = new ReflectionFunction($this->closure);
  }

  private function defineTokensFromFile(
  ): void {
    $this->rows = file( $this->reflectionFunction->getFileName());
  }

  private function findType(
    array $tokens,
    int $type
  ): int {
    foreach( $tokens as $cursor => $token ){
      if( $token[ 'type' ] === $type ){
        return $cursor;
      }
    }

    return -1;
  }

  private function namberToken(
    int $namberToken
  ): string {
    return match( $namberToken ){
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

  private function defineToken(
    string|array $tokenArgs
  ): array {
    [ $number, $value ] = is_string( $tokenArgs ) 
      ? [ ord( $tokenArgs ), $tokenArgs ] : $tokenArgs;

    return [
      'type' => $number,
      'text' => $value,
      'name' => $this->namberToken($number)
    ];
  }  

  private function defineTokensAll(
  ): void {
    $this->tokens = array_slice(
      token_get_all( 
        implode( '', $this->rows )
      ), 1
    );

    $this->tokens = array_map(
      fn(string|array $token) => (
        $this->defineToken($token)
      ), $this->tokens
    );

    $this->tokens = array_filter(
      $this->tokens, fn(array $token) => (
        !in_array($token['type'], [ T_WHITESPACE, T_DOT ])
      )
    );

    $this->tokens = array_values(
      $this->tokens
    );
  }

  private function defineMethod(
  ): void {
    $this->tokens = array_map(
      fn(string|array $token) => (
        $this->defineToken($token)
      ), array_slice( 
        token_get_all( 
          implode( '', array_slice(
            $this->rows, 0, 
            $this->reflectionFunction->getStartLine() - 2
          ))
        ), 1
      )
    );

    $this->tokens = array_filter(
      $this->tokens, fn(array $token) => (
        !in_array($token['type'], [ T_WHITESPACE, T_DOT ])
      )
    );

    $this->tokens = array_reverse(
      $this->tokens
    );

    [ $methodToken ] = array_slice(
      $this->tokens, array_search(
        T_FUNCTION, array_column(
          $this->tokens, 'type'
        )
      ) - 1
    );

    if( $methodToken ){
      $this->method = $methodToken[ 'text' ];
    }
  }

  private function defineClass(
  ): void {
    $this->tokens = array_map(
      fn(string|array $token) => (
        $this->defineToken($token)
      ), array_slice( token_get_all( 
          implode( '', $this->rows)
        ), 1
      )
    );

    $this->tokens = array_filter(
      $this->tokens, fn(array $token) => (
        !in_array( $token[ 'type' ], [ T_WHITESPACE, T_DOT ])
      )
    );    

    [ $classToken ] = array_slice( $this->tokens, array_search(
        T_CLASS, array_column( $this->tokens, 'type' )
      ) + 1
    );

    if( $classToken ){
      $this->classe = $classToken[ 'text' ];
    }
  }

  private function content(
  ): array {
    return array_values( 
      array_filter(
        array_map( fn(string|array $token ) => (
          $this->defineToken( $token )
        ), array_slice( token_get_all( 
            sprintf( '<?php %s', implode( '', array_slice(
              $this->rows, 
              $this->reflectionFunction->getStartLine() - 1,
              $this->reflectionFunction->getEndLine() - 
              $this->reflectionFunction->getStartLine() + 1
            )))
          ), 1
        )
      ), fn( array $token ) => (
          !in_array( $token[ 'type' ], [ T_WHITESPACE, T_DOT ])
      ))
    );
  }

  private function defineScopes(
  ): void {
    $this->tokens = $this->content();
    $this->scopes = array_slice( $this->tokens, 
      $this->findType( $this->tokens, T_FN ) + 2, 
    );

    $this->scopes = array_slice( $this->scopes, 
      0, $this->findType( $this->scopes, T_END_PARENTESES )
    );

    $this->scopes = array_map(
      fn(array $scope) => [
        'instance' => $scope[0][ 'text' ],
        'variable' => $scope[1][ 'text' ]
      ], array_chunk( $this->scopes, 2 )
    );
  }

  public function removerEnds(
    array $tokens,
    int $parenteses = 0
  ): array {
    for($i=0; $i<count($tokens); $i++){
      if($tokens[$i]['type'] === T_START_PARENTESES){
        $parenteses++;
      }

      if($tokens[$i]['type'] === T_END_PARENTESES){
        $parenteses--;

        if($parenteses < 0){
          $tokens = array_slice(
            $tokens, 0, $i
          ); break;
        }          
      }

      if($parenteses < 1){
        if($tokens[$i]['type'] === T_SEMICOLON){
          $tokens = array_slice(
            $tokens, 0, $i
          ); break;
        }
      }
    };

    return $tokens;
  }  

  private function defineBody(
  ): void {
    $this->tokens = $this->removerEnds(
      array_slice( $this->content(), $this->findType(
        $this->tokens, T_DOUBLE_ARROW
      ) + 1 )
    );
  }

  public function getClosure(
  ): array {
    $this->defineReflectFunction();
    $this->defineTokensFromFile();
    $this->defineTokensAll();
    $this->defineMethod();
    $this->defineClass();
    $this->defineScopes();
    $this->defineBody();

    return [ 
      'classe' => $this->classe,
      'method' => $this->method,
      'scopes' => $this->scopes, 
      'tokens' => $this->tokens,
    ];
  }
}