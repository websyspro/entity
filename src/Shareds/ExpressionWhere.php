<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use ReflectionFunction;
use function count, array_slice, is_string, in_array, ord, is_array;
use Websyspro\Entity\Enums\MetaType;

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
define( 'T_EXP_UNARY', 'ExpUnary' );
define( 'T_EXP_SUBQUERY', 'ExpSubQuery' );
define( 'T_EXP_FIELD', 'ExpField' );
define( 'T_EXP_EQUAL', 'ExpEqual' );
define( 'T_EXP_VALUE', 'ExpValue' );

class ExpressionWhere
{
  public array $uses = [];
  public array $scopes = [];
  public array $contexts = [];
  public array $tokens = [];
  public string $cacheClassKey;
  public string $cacheMethodKey; 
  public ReflectionFunction $reflectionFunction;
  
  public function __construct(
    public Closure $closure
  ){}

  private function inc(
    int $number
  ): int {
    return ++$number;
  }

  private function dec(
    int $number,
    int $decNumner = 0
  ): int {
    return (--$number) - $decNumner;
  }

  public static function where(
    array|object $array,
    Closure $closure,
    array $arrayFromArry = []
  ): array {
    foreach($array as $key => $val){
      if(is_numeric($key)){
        $closure($val, $key) ? $arrayFromArry[] = $val : [];
      } else {
        $closure($val, $key) ? $arrayFromArry[$key] = $val : [];
      }
    }

    return $arrayFromArry;
  }

  private function indexOf(
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

  private function getFileRows(
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

  private function getUsesRows(
  ): void {
    $this->uses = array_filter(
      $this->tokens, fn(string $token) => (
        str_starts_with( trim( $token), 'use')
      )
    );

    $this->uses = array_values(
      array_map( fn(string $token) => (
        str_replace([ 'use',';' ], '', $token)
      ), $this->uses )
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

  private function getUse(
    string $variable
  ): string|null {
    [ $uses ] = array_values( array_filter(
      $this->uses, fn(array $use) => $use[1] === $variable
    ));

    return $uses[0] ?? null;
  }

  private function getCacheKey(
    int $i = 0
  ): void {
    for($i=count($this->tokens) - 1; $i>=0; $i--){
      if(strpos($this->tokens[$i], 'function') !== false){
        if(isset($this->cacheMethodKey) === false){
          $this->cacheMethodKey = preg_replace([
            "#^.*function\s*#", "#\s*\(.*$#"
          ], "", trim($this->tokens[$i]));
        }
      }

      if(strpos($this->tokens[$i], 'class') !== false){
        if(isset($this->cacheClassKey) === false){
          $this->cacheClassKey = preg_replace([
            "#^.*class\s*#", "#\s*\{.*$#"
          ], "", trim($this->tokens[$i]));
        }
      }
    }
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

  private function createToken(
    string|array $tokenArgs
  ): array {
    [ $number, $value ] = is_string( $tokenArgs ) 
      ? [ ord( $tokenArgs ), $tokenArgs ] : $tokenArgs;

    return [ $number, $value, $this->namberToken($number)];
  }
  
  public function getContextsNotEnds(
    array $tokens,
    int $parenteses = 0
  ): array {
    for($i=0; $i<count($tokens); $i++){
      if($tokens[$i][0] === T_START_PARENTESES){
        $parenteses++;
      }

      if($tokens[$i][0] === T_END_PARENTESES){
        $parenteses--;

        if($parenteses < 0){
          $tokens = array_slice(
            $tokens, 0, $i
          ); break;
        }          
      }

      if($parenteses < 1){
        if($tokens[$i][0] === T_SEMICOLON){
          $tokens = array_slice(
            $tokens, 0, $i
          ); break;
        }
      }
    };

    return $tokens;
  }
  
  private function createContext(
    array $contexts = []
  ): array {
    return $this->getContextsNotEnds(
      array_slice( $contexts, $this->inc(
        $this->indexOf( $contexts, T_FN )
      ))
    );
  }

  private function getContexts(
  ): void {
    $this->contexts = array_slice(
      $this->tokens, 
      $this->reflectionFunction->getStartLine() - 1,
      $this->reflectionFunction->getEndLine() - 
      $this->reflectionFunction->getStartLine() + 1
    );

    $this->contexts = array_slice(
      token_get_all(
        sprintf( '<?php %s', implode(
          '', $this->contexts
        ))
      ), 1
    );

    $this->contexts = array_map(
      fn(string|array $token) => (
        $this->createToken($token)
      ), $this->contexts
    );

    $this->contexts = array_filter(
      $this->contexts, fn(array $token) => !in_array( 
        $token[0], [ T_WHITESPACE, T_CURLY_OPEN, T_END_BRACE, T_DOT ]
      )
    );

    $this->contexts = array_values($this->contexts);
    $this->contexts = $this->createContext($this->contexts);
  }

  private function getScopesByContext(
    array $contexts,
    array $scopes = [],
    array $scopesPaarent = []
  ): array {
    $scopes = $this->groupByTypes(
      array_slice( $contexts, 1, $this->dec(
          $this->indexOf( $contexts, T_END_PARENTESES )
      )), [ T_COMMA ]
    );

    $scopes = array_map(
      function(array $scope){
        [ $instance, $variable ] = $scope;
        $instance = $this->getUse(
          $instance[1]
        );

        return [ 
          $instance,
          'table', 
          // $instance::meta(
          //   MetaType::Query
          // )->entity[1], 
          $variable[1]
        ];
      }, $scopes
    );

    return [ ...$scopesPaarent, ...$scopes ];
  }

  private function groupByLogicals(
    array $tokens
  ): array {
    return $this->groupByTypes(
      $tokens, [ T_LOGICAL_AND, T_LOGICAL_OR, T_BOOLEAN_AND, T_BOOLEAN_OR ], true
    );
  }

  private function groupByEquals(
    array $tokens
  ): array {
    return $this->groupByTypes(
      $tokens, [ 
        T_EQUAL,
        T_IS_EQUAL,
        T_IS_IDENTICAL,
        T_IS_NOT_EQUAL,
        T_IS_NOT_IDENTICAL,
        T_IS_GREATER_OR_EQUAL,
        T_IS_SMALLER_OR_EQUAL,
        T_GREATER_THAN,
        T_LESS_THAN
      ], true
    );
  }

  private function isField(
    array $tokens = []
  ): bool {
    if( count( $tokens ) < 3 ){
      return false;
    }

    [ $tokenA, $tokenB, $tokenC 
    ] = $tokens;
    
    return $tokenA[0] === T_VARIABLE 
        && $tokenB[0] === T_OBJECT_OPERATOR
        && $tokenC[0] === T_STRING;
  }
  
  private function getField(
    array $scopes = [],
    array $tokens = []    
  ): array {
    [ $variable, $field ] = [
      $tokens[0][1], 
      $tokens[2][1]
    ];

    $scopes = $this->where( $scopes, 
      fn(array $scope) => $scope[2] === $variable
    );

    if(count( $scopes ) !== 0){
      [ $instance, $table ] = $scopes[0];
      return [ T_EXP_FIELD, $table, $field, 
        // $instance::meta(MetaType::Query)->types[ $field ]
      ];
    }

    return $tokens;
  }

  private function equalReverse(
    array $tokens
  ): array {
    [ $type, $token ] = $tokens;
    return [ $type, match( $token[0]){
      T_IS_SMALLER_OR_EQUAL => $this->createToken( ">=" ),
      T_IS_GREATER_OR_EQUAL => $this->createToken( "<=" ),
      T_GREATER_THAN => $this->createToken( "<" ),
      T_LESS_THAN => $this->createToken( ">" ), 
        default => $token
    }];
  }  

  private function getEqual(
    array $tokens = []    
  ): array {
    [ $token ] = $tokens;
    return [T_EXP_EQUAL, $token];
  }  

  private function getValue(
    array $tokens = []    
  ): array {
    return [T_EXP_VALUE, $tokens];
  }  
  
  private function getParseFieldAndValue(
    array $scopes = [],
    array $tokens = []
  ): array {
    [ $left, $equal, $right ] = $tokens;
    [ $left, $equal, $right ] = [ 
      $this->isField($left) 
        ? $this->getField($scopes, $left) 
        : $this->getValue($left),
          $this->getEqual($equal),
      $this->isField($right) 
        ? $this->getField($scopes, $right) 
        : $this->getValue($right)
    ];

    if($left[0] === T_EXP_VALUE){
      $equal = $this->equalReverse($equal);
      return [$right, $equal, $left];
    } else return [$left, $equal, $right];
  }

  private function getTokensByContext(
    array $contexts,
    array $tokens = []
  ): array {
    $tokens = array_slice(
      $contexts, $this->inc(
        $this->indexOf( $contexts, T_DOUBLE_ARROW )
      )
    );

    $tokens = $this->groupByLogicals($tokens);
    // TODO for agrupar compare for to Between
    return $tokens;
  }

  private function isDenying(
    array $tokens = []
  ): bool {
    return $tokens[0][0] === T_NOT;
  }
  
  private function isGroup(
    array $tokens = []
  ): bool {
    return $tokens[0][0] === T_START_PARENTESES;
  }

  private function getSubQueryMethod(
    array $tokens = []    
  ): array|null {
    [ $subQueryMethod ] = array_slice(
      $tokens, $this->dec(
        $this->indexOf(
          $tokens, T_FN), 1
      ), 1
    );

    return $subQueryMethod ?? null;
  }  

  private function isSubQuery(
    array $tokens = []    
  ): bool {
    $subQueryMethod= $this->getSubQueryMethod($tokens);
    return in_array( $subQueryMethod[1], ['any']);
  }
  
  private function isLogical(
    array $tokens = []
  ): bool {
    return in_array($tokens[0][0], [
      T_LOGICAL_AND, T_LOGICAL_OR, T_BOOLEAN_AND, T_BOOLEAN_OR 
    ]);
  }
  
  private function isCompare(
    array $tokens = []
  ): bool {
    return empty(
      array_filter( $tokens, fn(array $token) => in_array( $token[0], [
        T_IS_NOT_IDENTICAL, T_IS_GREATER_OR_EQUAL, T_IS_SMALLER_OR_EQUAL,
        T_EQUAL, T_IS_EQUAL, T_IS_IDENTICAL, T_IS_NOT_EQUAL,
        T_GREATER_THAN, T_LESS_THAN
      ]))
    ) ? false : true;
  }

  private function isUnary(
    array $tokens = []
  ): bool {
    return $this->isCompare($tokens) === false;
  }  
  
  private function getParser(
    string $parent,
     array $contexts = [],
     array $scopes = [],    
  ): array {
    foreach($contexts as $i => $tokens){
      if($this->isDenying($tokens)){
        $contexts[$i] = [T_EXP_DENYING, $parent, $scopes, $this->getParser(
          T_EXP_DENYING, $this->groupByLogicals(array_slice( $tokens, 1 )), $scopes
        )];
      } else if($this->isGroup($tokens)){
        $tokens = $this->groupByLogicals( array_slice( $tokens, 1, -1 ));
        // TODO for agrupar compare for to Between
        $contexts[$i] = [T_EXP_GROUP, $parent, $scopes, $this->getParser(
          T_EXP_GROUP, $tokens, $scopes
        )];
      } else if($this->isSubQuery($tokens)){
        [ $_ ,$method ] = $this->getSubQueryMethod($tokens);
        $tokens = $this->createContext($tokens);
        $scopes = $this->getScopesByContext($tokens, [], $scopes);
        $tokens = $this->getTokensByContext($tokens);
        $tokens = $this->getParser(T_EXP_SUBQUERY, $tokens, $scopes);
        $contexts[$i] = [T_EXP_SUBQUERY, $parent, $method, $scopes, $tokens];
      } else if($this->isLogical($tokens)){
        [ $token ] = $tokens;
        $contexts[$i] = [T_EXP_LOGICAL, $parent, $token];
      } else if($this->isCompare($tokens)){
        $tokens = $this->groupByEquals($tokens);
        $tokens = $this->getParseFieldAndValue( $scopes, $tokens );
        $contexts[$i] = [T_EXP_COMPARE, $parent, $scopes, $tokens];
      } else if($this->isUnary( $tokens )){
        $tokens = $this->getField($scopes, $tokens);
        $contexts[$i] = [T_EXP_UNARY, $parent, $scopes, $tokens];
      } 
    }

    return $contexts;
  }

  private function getBuilds(
  ): void {
    $this->scopes = $this->getScopesByContext($this->contexts);
    $this->tokens = $this->getTokensByContext($this->contexts);
    $this->tokens = $this->getParser(
      T_EXP_INITIAL, $this->tokens, $this->scopes
    );

    /* clear variable(s) */
    unset($this->reflectionFunction);
    unset($this->contexts);
    unset($this->closure);   
  }

  private function startups(
  ): void {
    $this->getFileRows();
    $this->getUsesRows();
    $this->getCacheKey();
    $this->getContexts();
    $this->getBuilds();
  }

  public function get(
  ): mixed {
    $this->startups();
    return $this;
  }
}