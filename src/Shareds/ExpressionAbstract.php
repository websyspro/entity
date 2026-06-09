<?php

namespace Websyspro\Entity\Shareds;

use function in_array, count, array_slice;
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
  public ExpressionType $expressionType;  
  public ReflectionFunction $reflectionFunction;
  public string $cacheClassKey;
  public string $cacheMethodKey;
  public array $scopes = [];
  public array $statics = [];
  public array $params = [];  
  public array $contexts = [];
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
 
  public function getCacheKey(
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
  
  private function namberToken(
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

  private function createToken(
    string|array $tokenArgs
  ): array {
    [ $number, $value ] = is_string( $tokenArgs ) 
      ? [ ord( $tokenArgs ), $tokenArgs ] : $tokenArgs;

    if( in_array( $number, [ T_CONSTANT_ENCAPSED_STRING ])){
      $value = trim( $value, '"\'' );
    }  

    return [ $number, $value, $this->namberToken($number)];
  }  
  
  public function getContext(
    array $contexts = []
  ): array {
    return $this->getContextsNotEnds(
      array_slice( $contexts, $this->inc(
        $this->indexOf( $contexts, T_FN )
      ))
    );
  }

  public function getContexts(
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

    $this->contexts = array_values(
      array_filter(
        $this->contexts, fn(array $token) => !in_array( 
          $token[0], [ T_WHITESPACE, T_CURLY_OPEN, T_END_BRACE, T_DOT ]
        )
      )
    );

    $this->contexts = $this->getContext($this->contexts);
  }
  
  public function getStatics(
  ): void {
    $this->statics = $this->reflectionFunction
      ->getStaticVariables();
  }

  public function getGroupByLogicals(
    array $tokens
  ): array {
    return $this->groupByTypes(
      $tokens, [ T_LOGICAL_AND, T_LOGICAL_OR, T_BOOLEAN_AND, T_BOOLEAN_OR ], true
    );
  }  

  public function getScopesByContext(
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

        $metadata = new EntityStructure($instance);
        $metadata = $metadata->get();

        return [ 
          $instance,
          $metadata->contexts[T_Entity][0],
          $variable[1]
        ];
      }, $scopes
    );

    return [ ...$scopesPaarent, ...$scopes ];
  }

  public function getTokensByContext(
    array $contexts,
    array $tokens = []
  ): array {
    $tokens = array_slice(
      $contexts, $this->inc(
        $this->indexOf( $contexts, T_DOUBLE_ARROW )
      )
    );

    if( $this instanceof ExpressionWhere ){
      $tokens = $this->getGroupByLogicals($tokens);
    }

    return $tokens;
  } 
  
  public function getFieldMethods(
    array $contexts = []
  ): array {
    $methods = $this->groupByTypes(
      array_slice( $contexts, 4 ), [
        T_OBJECT_OPERATOR
      ]
    );

    $methods = array_map(
      function(array $contexts){
        [ $name ] = $contexts;
        $args = $this->groupByTypes(
          array_slice(
            $contexts, $this->inc(
              $this->indexOf(
                $contexts, T_START_PARENTESES
              )
            ), -1
          ), [ T_COMMA ]
        );

        return [ $name[1], $args ];
      }, $methods
    );

    $compareListMethods = [
      'contains',
      'startsWith',
      'endsWith'
    ];

    $modifyMethods = array_filter(
      $methods, fn(array $method) => in_array(
        $method[0], $compareListMethods
      ) === false
    );

    $compareMethods = array_filter(
      $methods, fn(array $method) => in_array(
        $method[0], $compareListMethods
      ) === true
    );

    return [ array_values( $modifyMethods ), array_values( $compareMethods )];
  }   

  public function getBuildClear(
  ): void {
    unset($this->reflectionFunction);
    unset($this->expressionType);
    unset($this->contexts);
    unset($this->statics);
    unset($this->closure);
  }  
  
  public function isPossibleToCache(
  ): bool {
    return isset($this->cacheClassKey)
        && isset($this->cacheMethodKey);
  }

  public function getCache(
  ): string {
    $cacheClassKey = md5($this->cacheClassKey);
    $cacheMethodKey = md5($this->cacheMethodKey);
    return "orm-$cacheClassKey-$cacheMethodKey";
  }  

  public function getParserInitial(
    array $scopes = [],
    array $tokens = []
  ): array {
    return $tokens;    
  }

  public function getParserValues(
    array $contexts = []
  ): array {
    return $contexts;
  } 
  
  public function getBuildsDirect(
  ): void {
    $this->scopes = $this->getScopesByContext( $this->contexts );
    $this->tokens = $this->getTokensByContext( $this->contexts );
    $this->tokens = $this->getParserInitial(
      $this->scopes, $this->tokens
    );

    Cache::save(
      $this->getCache(), [
        'hash' => md5( serialize( $this->contexts)),
        'context' => [
          'scopes' => $this->scopes,
          'tokens' => $this->tokens
        ]
      ]     
    );

    $this->tokens = $this->getParserValues($this->tokens);
    $this->getBuildClear();
  }

  public function getBuilds(
  ): void {
    if($this->isPossibleToCache()){
      if(Cache::exist($this->getCache())){
        [ 'hash' => $hash, 'context' => $context 
        ] = Cache::load( $this->getCache());
        if( $hash === md5( serialize( $this->contexts ))){
          $this->scopes = $context['scopes'];
          $this->tokens = $this->getParserValues( $context['tokens']);
          $this->getBuildClear();
        } else $this->getBuildsDirect();
      } else $this->getBuildsDirect();
    } else $this->getBuildsDirect();
  }
  
  public function startups(
  ): void {
    $this->getFileRows();
    $this->getStatics();
    $this->getCacheKey();
    $this->getUsesRows();
    $this->getContexts();
    $this->getBuilds();
  }  
}