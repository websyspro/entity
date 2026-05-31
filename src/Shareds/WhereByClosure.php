<?php

namespace Websyspro\Entity\Shareds;

use Closure;
// use ReflectionFunction;

// use function ord, array_slice, in_array, sprintf, is_string, count;
// use Websyspro\Entity\Enums\MetaType;

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

class WhereByClosure
{
  // private ReflectionFunction $reflectionFunction;
  // private string|array $tokens = [];
  // private array $scopes = [];
  // private array $uses = [];
  // private array $rows = [];
  // private string $classe = '';
  // private string $method = '';
  // private string $caches = '';
  
  public function __construct(
    public Closure $closure
  ){}

  private function defineReflectFunction(
  ): void {
    $this->reflectionFunction = new ReflectionFunction($this->closure);
  }

  private function defineTokensFromFile(
  ): void {
    $this->rows = array_values(
      array_filter(
        file( $this->reflectionFunction->getFileName()), 
          fn( string $row ) => !str_starts_with( ltrim( $row ), '//' )
      )
    );
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

  private function findTypeEqual(
    array $tokens = []
  ): int {
    foreach( $tokens as $i => $token ){
      if( $token['type'] === T_EQUAL ) return $i;
      if( $token['type'] === T_IS_EQUAL ) return $i;
      if( $token['type'] === T_IS_IDENTICAL ) return $i;
      if( $token['type'] === T_IS_NOT_EQUAL ) return $i;
      if( $token['type'] === T_IS_NOT_IDENTICAL ) return $i;
      if( $token['type'] === T_IS_GREATER_OR_EQUAL ) return $i;
      if( $token['type'] === T_IS_SMALLER_OR_EQUAL ) return $i;
      if( $token['type'] === T_GREATER_THAN ) return $i;
      if( $token['type'] === T_LESS_THAN ) return $i;
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
      $this->method = $methodToken['text'];
    }
  }

  private function defineClass(
  ): void {
    $this->tokens = array_map(
      fn(string|array $token) => (
        $this->defineToken($token)
      ), array_slice( token_get_all( 
          implode( '', $this->rows )
        ), 1
      )
    );

    $this->tokens = array_filter(
      $this->tokens, fn(array $token) => (
        !in_array( $token['type'], [ T_WHITESPACE, T_DOT ])
      )
    );    

    [ $classToken ] = array_slice( $this->tokens, array_search(
        T_CLASS, array_column($this->tokens, 'type')
      ) + 1
    );

    if( $classToken ){
      $this->classe = $classToken['text'];
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
          !in_array( $token['type'], [ 
            T_WHITESPACE, T_DOT, T_CURLY_OPEN, T_END_BRACE 
          ])
      ))
    );
  }

  private function defineScopesParse(
    array $scopes = []
  ): array {
    return array_map(
      function(array $scope){
        [ $instance, $variable ] = $scope;
        [ $instance ] = array_values( array_filter( 
          $this->uses, fn( array $use ) => $use['key'] === $instance['text']
        ));

        return [ 
          'instance' => $instance['use'],
          'variable' => $variable['text']
        ];
      }, array_chunk( $scopes, 2 )
    );
  }

  private function defineScopes(
  ): void {
    $this->tokens = $this->content();
    $this->scopes = array_slice( $this->tokens, 
      $this->findType( $this->tokens, T_FN ) + 2, 
    );

    $this->scopes = array_filter(
      array_slice( $this->scopes, 
        0, $this->findType( $this->scopes, T_END_PARENTESES )
      ), fn( array $token ) => !in_array( $token['type'], [ T_COMMA ])
    );

    $this->scopes = $this->defineScopesParse(
      $this->scopes
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

  private function defineCaches(
  ): void {
    $this->caches = class_exists( $this->classe ) 
      && method_exists( $this->classe, $this->method ) 
       ? 'yes' : 'no';
  }

  public function groupByUse(
    array $tokens = [],
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if( $token['type'] === T_USE && $depth === 0){
        if( $curr ){
          $accu[] = $curr;
          $curr = [];
        } continue;
      }

      $curr[] = $token;

      if($token['type'] === T_START_PARENTESES) $depth++;
      if($token['type'] === T_END_PARENTESES) $depth--;
    }

    if( $curr ){
      $accu[] = $curr;
    }

    return $accu;
  }
  
  private function createUse(
    array $useArr = []
  ): array {
    [ $use ] = $useArr;
    [ $key ] = array_slice(
      explode('\\', $use), -1
    );

    return [ 'use' => $use, 'key' => $key ];
  }

  private function createUseByAs(
    array $useArr = []
  ): array {
    [ $use, $key ] = $useArr;
    return [ 'use' => $use, 'key' => $key ];
  }  

  private function defineUses(
  ): void {
    $this->uses = array_map(
      fn(string|array $token) => (
        $this->defineToken($token)
      ), array_slice(
        token_get_all(
          sprintf( '<?php %s', implode( '', array_filter(
            $this->rows, fn(string $row) => str_starts_with(
              trim( $row), "use"
            )
          )))
        ), 1
      )
    );

    $this->uses = array_filter($this->uses, fn( array $token ) => (
      !in_array( $token['type'], [ T_WHITESPACE, T_SEMICOLON, T_DOT ])
    ));

    $this->uses = array_map(
      fn(array $use) => (
        count($use) === 1 
          ? $this->createUse( array_map( fn(array $token) => $token['text'], $use)) 
          : $this->createUseByAs(
              array_map( fn(array $token) => $token['text'], 
                array_values(
                  array_filter( $use, fn(array $token) => (
                    !in_array( $token['type'], [ T_AS ]))
                  )
                )
              )
            )
      ), $this->groupByUse($this->uses)
    );
  }

  public function isLogical(
    array $token
  ): bool {
    if(!isset( $token['type'])){
      return false;
    }

    return $token['type'] === T_LOGICAL_AND
        || $token['type'] === T_LOGICAL_OR
        || $token['type'] === T_BOOLEAN_AND
        || $token['type'] === T_BOOLEAN_OR;
  }

  public function isEqual(
    array $token
  ): bool {
    if(!isset( $token['type'])){
      return false;
    }

    return $token['type'] === T_EQUAL
        || $token['type'] === T_IS_EQUAL
        || $token['type'] === T_IS_IDENTICAL
        || $token['type'] === T_IS_NOT_EQUAL
        || $token['type'] === T_IS_NOT_IDENTICAL
        || $token['type'] === T_IS_GREATER_OR_EQUAL
        || $token['type'] === T_IS_SMALLER_OR_EQUAL
        || $token['type'] === T_GREATER_THAN
        || $token['type'] === T_LESS_THAN;
  } 
  
  public function isOperatorObject(
    array $token
  ): bool {
    if(!isset( $token['type'])){
      return false;
    }

    return $token['type'] === T_OBJECT_OPERATOR;
  }  

  public function groupByLogical(
    array $tokens = [],
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if( $this->isLogical($token) && $depth === 0){
        if( $curr ){
          $accu[] = $curr;
          $curr = [];
        }

        $accu[] = [ $token ];
        continue;
      }

      $curr[] = $token;

      if($token['type'] === T_START_PARENTESES) $depth++;
      if($token['type'] === T_END_PARENTESES) $depth--;
    }

    if( $curr ){
      $accu[] = $curr;
    }

    return $accu;
  }

  public function groupByEqual(
    array $tokens = [],
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if( $this->isEqual($token) && $depth === 0){
        if( $curr ){
          $accu[] = $curr;
          $curr = [];
        }

        $accu[] = [ $token ];
        continue;
      }

      $curr[] = $token;

      if($token['type'] === T_START_PARENTESES) $depth++;
      if($token['type'] === T_END_PARENTESES) $depth--;
    }

    if( $curr ){
      $accu[] = $curr;
    }

    return $accu;
  }  

  private function removerParentesesFromGroup(
    array $tokens = []
  ): array {
    return array_slice( $tokens, 1, -1 );
  }

  private function removerNot(
    array $tokens = []
  ): array {
    return array_slice( $tokens, 1 );
  }  

  private function eventBySubQuery(
    array $tokens = []
  ): string|null {
    $tokenTfn = $this->findType($tokens, T_FN);
    if( $tokenTfn === -1 ){
      return null;
    }

    [ 'text' => $eventBySubQuery 
    ] = $tokens[ $tokenTfn - 2 ];

    return $eventBySubQuery;
  }

  private function scopesBySubQuery(
    array $tokens = [],
    array $scopes = []
  ): array {
    $tokens = array_slice( $tokens, $this->findType( $tokens, T_FN ) + 2);
    $tokens = array_slice( $tokens, 0, $this->findType( $tokens, T_END_PARENTESES ));
    return array_merge( $scopes, $this->defineScopesParse( $tokens ) );
  }

  private function tokensBySubQuery(
    array $tokens = []
  ): array {
    $tokens = array_slice( $tokens, $this->findType( $tokens, T_DOUBLE_ARROW ) + 1);
    return $this->removerEnds( $tokens );
  }

  private function hasNegative(
    array $tokens = []
  ): bool {
    return $this->findType( array_slice( $tokens, 0, 1 ), T_NOT ) !== -1;
  }   

  private function hasGroup(
    array $tokens = []
  ): bool {
    [ $token ] = $tokens;
    return $token['type'] === T_START_PARENTESES;
  }

  private function hasSubQuery(
    array $tokens = []
  ): bool {
    $eventBySubQuery = $this->eventBySubQuery($tokens);
    if( $eventBySubQuery === null ){
      return false;
    }

    return in_array( $eventBySubQuery, [ "any" ]) && $this->findType( $tokens, T_FN );
  }

  private function hasLogincal(
    array $tokens = []
  ): bool {
    [ $token ] = $tokens;
    return $this->isLogical( $token );
  }

  private function hasCompare(
    array $tokens = []
  ): bool {
    return $this->findTypeEqual( $tokens ) !== -1;
  }

  private function hasUnary(
    array $tokens = []
  ): bool {
    return $this->findTypeEqual( $tokens ) === -1;
  }  

  private function createExpNode(
    string $parent,
    array $tokens = [],
    array $scopes = [],
  ): array {
    $tokens = $this->groupByLogical( $tokens );
    $tokens = $this->parserTokens( 'ExpNode', $tokens, $scopes );
    return [ 'object' => 'ExpNode', 'parent' => $parent, 'scopes' => $scopes, 'tokens' => $tokens ];
  }

  private function createExpNeg(
    string $parent,
    array $tokens = [],
    array $scopes = []
  ): array {
    $tokens = $this->groupByLogical( $tokens );
    $tokens = $this->parserTokens( 'ExpNeg', $tokens, $scopes );    
    return [ 'object' => 'ExpNeg', 'parent' => $parent, 'scopes' => $scopes, 'tokens' => $tokens ];
  }   

  private function createExpGroup(
    string $parent,
    array $tokens = [],
    array $scopes = []
  ): array {
    $tokens = $this->groupByLogical( $tokens );
    $tokens = $this->parserTokens( 'ExpGroup', $tokens, $scopes );
    $tokens = $this->simplesTokens( $tokens );
    return [ 'object' => 'ExpGroup', 'parent' => $parent, 'scopes' => $scopes, 'tokens' => $tokens ];
  }
  
  private function createExpSubQuery(
    string $parent,
    string|null $event,
    array $scopes = [],
    array $tokens = [],
  ): array {
    $tokens = $this->groupByLogical( $tokens );
    $tokens = $this->parserTokens( 'ExpSubQuery', $tokens, $scopes );
    return [ 'object' => 'ExpSubQuery', 'parent' => $parent, 'event' => $event, 'scopes' => $scopes, 'tokens' => $tokens ];
  }

  private function createExpLog(
    string $parent,
    array $tokens = []
  ): array {
    return [ 'object' => 'ExpLog', 'parent' => $parent, 'tokens' => $tokens ];
  }
  
  private function createExpUnary(
    string $parent,
    array $tokens = [],
    array $scopes = []
  ): array {
    $tokens = $this->createExpField( $tokens, $scopes );  
    return [ 'object' => 'ExpUnary', 'parent' => $parent, 'scopes' => $scopes, 'tokens' => $tokens ];
  }

  private function hasField(
    array $tokens = []
  ): bool {
    if( count( $tokens ) < 3 ){
      return false;
    }

    [ $tokenA, $tokenB, $tokenC 
    ] = $tokens;
    
    return $tokenA['type'] === T_VARIABLE 
        && $tokenB['type'] === T_OBJECT_OPERATOR
        && $tokenC['type'] === T_STRING;
  }

  private function findInstance(
    array $variable = [],
    array $scopes = []
  ): string {
    $instance = array_filter(
      $scopes, fn( array $scope ) => (
        $scope[ 'variable' ] === $variable[ 'text' ]
      )
    );

    [ $instanceObject
    ] = array_values( $instance ); 
    return $instanceObject[ 'instance' ];
  }

  public function groupByOperatorObject(
    array $tokens = [],
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if( $this->isOperatorObject($token) && $depth === 0){
        if( $curr ){
          $accu[] = $curr;
          $curr = [];
        } continue;
      }

      $curr[] = $token;

      if($token['type'] === T_START_PARENTESES) $depth++;
      if($token['type'] === T_END_PARENTESES) $depth--;
    }

    if( $curr ){
      $accu[] = $curr;
    }

    return array_map(
      function( array $tokens ){
        [ $name ] = array_slice(
          $tokens, 0, 1
        );

        return [ 
          'name' => $name[ 'text' ],
          'type' => in_array( $name[ 'text' ], [
            "startWith", "endWith", "contains" 
          ]) ? "compare" : "modify", 
          'args' => count( $tokens ) > 3
            ? array_slice( $tokens, 2, -1 ) 
            : []
        ];
      }, $accu
    );
  }  

  private function createExpField(
    array $tokens = [],
    array $scopes = []
  ): array {
    [ $variable, $_, $variableName 
    ] = $tokens;

    $instance = $this->findInstance( $variable, $scopes );
    if( class_exists( $instance )){
      $entityStructure = $instance::meta(
        MetaType::Query
      );

      [ $table, $field, $type, $events ] = [
        $entityStructure->entity[ 'alias' ],
        $entityStructure->alias[
          $variableName[ 'text' ]
        ] ?? $variableName[ 'text' ],
        $entityStructure->types[
          $variableName[ 'text' ]
        ], $this->groupByOperatorObject(
          array_slice( $tokens, 4 )
        )
      ];

      return [
        'object' => 'ExpField',
        'events' => $events,
        'table' => $table,
        'field' => $field,
        'type' => $type
      ];
    }

    return [
      'object' => 'ExpField',
      'events' => 'notFound', 
      'table' => 'notFound',
      'field' => 'notFound',
      'type' => 'notFound'
    ];
  }

  private function createExpEqual(
    array $tokens = []
  ): array {
    return [ 'object' => 'ExpEqual', 'tokens' => $tokens ];
  }

  private function createExpValue(
    array $tokens = []
  ): array {
    $islist = $this->findType(
      array_slice( $tokens, 0, 1 ),
        T_START_BRACKET
    ) !== -1;

    return [ 'object' => 'ExpValue', 'islist' => $islist, 'tokens' => $tokens ];
  }

  private function equalReverse(
    array $tokens
  ): array {
    [ 'tokens' => $tokens ] = $tokens;
    [ $tokens ] = $tokens;

    $tokens = match( $tokens[ 'type' ]){
      T_IS_SMALLER_OR_EQUAL => $this->defineToken( ">=" ),
      T_IS_GREATER_OR_EQUAL => $this->defineToken( "<=" ),
      T_GREATER_THAN => $this->defineToken( "<" ),
      T_LESS_THAN => $this->defineToken( ">" ), 
        default => $tokens
    };

    return [ 'object' => 'ExpEqual', 'tokens' => [ $tokens ]];
  }
  
  private function createExpFieldOrValue(
    array $tokens = [],
    array $scopes = []
  ): array {
    [ $left, $equal, $right ] = $tokens;
    [ $left, $equal, $right ] = [ 
      $this->hasField( $left ) 
        ? $this->createExpField( $left, $scopes )
        : $this->createExpValue( $left ), 
      $this->createExpEqual( $equal ),
      $this->hasField( $right ) 
        ? $this->createExpField( $right, $scopes ) 
        : $this->createExpValue( $right )
    ];

    $compareEvents = $left['object'] === 'ExpValue' 
      ? $right : $left;

    $compareEvents = array_filter(
      $compareEvents['events'], 
        fn( array $event ) => $event['type'] === 'compare'
    );  

    if( empty( $compareEvents )){
      if( $left[ 'object' ] === 'ExpValue' ){
        $equal = $this->equalReverse( $equal );
        return [ $right, $equal, $left ];
      } else return [ $left, $equal, $right ]; 
    } else return $left[ 'object' ] === 'ExpValue'
      ? [ $right ] : [ $left ];
  }

  private function createExpCompare(
    string $parent,
    array $tokens = [],
    array $scopes = [],
  ): array {
    $tokens = $this->groupByEqual( $tokens );
    $tokens = $this->createExpFieldOrValue( $tokens, $scopes );
    return [ 'object' => 'ExpCompare', 'parent' => $parent, 'scopes' => $scopes, 'tokens' => $tokens ];
  }  

  private function parserTokens(
    string $parent,
    array $tokens = [],
    array $scopes = [],
  ): array {
    foreach( $tokens as $i => $token ){
      if( $this->hasNegative( $token )){
        $tokens[$i] = $this->createExpNeg( 
          $parent, $this->removerNot( $token ), $scopes
        );
      } else
      if( $this->hasGroup( $token )){
        $tokens[$i] = $this->createExpGroup( 
          $parent, $this->removerParentesesFromGroup( $token ), $scopes
        );
      } else
      if( $this->hasSubQuery( $token )){
        $tokens[$i] = $this->createExpSubQuery( 
          $parent, 
          $this->eventBySubQuery( $token ), 
          $this->scopesBySubQuery( $token, $scopes ),
          $this->tokensBySubQuery( $token )
        );
      } else
      if( $this->hasLogincal( $token )){
        $tokens[$i] = $this->createExpLog( $parent, $token );
      } else
      if( $this->hasCompare( $token )){
        $tokens[$i] = $this->createExpCompare( $parent, $token, $scopes );
      } else 
      if( $this->hasUnary( $token )){
        $tokens[$i] = $this->createExpUnary( $parent, $token, $scopes );
      }
    }

    return $tokens;
  }

  private function simplesTokens(
    array $tokens = []
  ): array {
    return $tokens;
  }

  private function defineTokens(
  ): void {
    $this->tokens = $this->removerEnds(
      array_slice( $this->content(), $this->findType(
        $this->content(), T_DOUBLE_ARROW
      ) + 1 )
    );

    $this->tokens = $this->createExpNode(
      'ExpInit', $this->tokens, $this->scopes
    );
  }

  private function saveArgs(
    array $contexts = []
  ): array {
    return $contexts;
  }

  private function getArgs(
  ): array {
    return [ 
      'uses'   => $this->uses,
      'classe' => $this->classe,
      'method' => $this->method,
      'caches' => $this->caches,
      'scopes' => $this->scopes, 
      'tokens' => $this->tokens,
    ];    
  }

  public function getClosure(
  ): array {
    $this->defineReflectFunction();
    $this->defineTokensFromFile();
    $this->defineClass();
    // $this->defineMethod();
    // $this->defineCaches();
    // $this->defineTokensAll();
    $this->defineUses();
    // $this->defineScopes();
    $this->defineTokens();
    return $this->getArgs();
  }
}