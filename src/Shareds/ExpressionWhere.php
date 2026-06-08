<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use ReflectionFunction;
use function count, array_slice, is_string, in_array, ord, is_array, is_object, sprintf, defined;
use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Core\DB;

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

class ExpressionWhere
{
  public array $uses = [];
  public array $scopes = [];
  public array $contexts = [];
  public array $tokens = [];
  public array $statics = [];
  public array $params = [];
  public string $cacheClassKey;
  public string $cacheMethodKey; 
  public ReflectionFunction $reflectionFunction;
  public ExpressionType $expressionType;
  
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

  private function getUse(
    string $variable
  ): string|null {
    [ $uses ] = array_values(
      array_filter(
        $this->uses, fn(array $use) => $use[1] === $variable
      )
    );

    return $uses[0] ?? null;
  }

  private function getStatics(
  ): void {
    $this->statics = $this->reflectionFunction
      ->getStaticVariables();
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
  
  private function getContext(
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

    $this->contexts = array_values(
      array_filter(
        $this->contexts, fn(array $token) => !in_array( 
          $token[0], [ T_WHITESPACE, T_CURLY_OPEN, T_END_BRACE, T_DOT ]
        )
      )
    );

    $this->contexts = $this->getContext($this->contexts);
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

  private function getGroupByLogicals(
    array $tokens
  ): array {
    return $this->groupByTypes(
      $tokens, [ T_LOGICAL_AND, T_LOGICAL_OR, T_BOOLEAN_AND, T_BOOLEAN_OR ], true
    );
  }

  private function getGroupByTypes(
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
    array $tokens = [],
    array $scopes = []
  ): bool {
    if( count( $tokens ) < 3 ){
      return false;
    }

    [ $tokenA, $tokenB, $tokenC 
    ] = $tokens;

    
    $scopes = array_filter(
      $scopes, fn(array $scope) => $scope[2] === $tokenA[1]
    );
    
    if( empty( $scopes )){
      return false;
    }
    
    return $tokenA[0] === T_VARIABLE 
        && $tokenB[0] === T_OBJECT_OPERATOR
        && $tokenC[0] === T_STRING;
  }

  private function getType(
    string $type
  ): string {
    [ $type ] = array_reverse(
      explode( '\\', $type )
    );

    return $type;
  }

  private function getFieldType(
    string $instance,
    string $field
  ): string {
    $metadata = new EntityStructure( $instance );
    return $this->getType( $metadata->get()->contexts['types'][ $field ]);    
  }

  private function getFieldMethods(
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
  
  private function getField(
    array $scopes = [],
    array $contexts = [], 
  ): array {
    [ $_, $variable ] = $contexts[0];
    [ $_, $field ] = $contexts[2];

    [ $scopes ] = array_values(
      array_filter( $scopes, 
        fn(array $scope) => $scope[2] === $variable
      )
    );

    [ $instance, $table
    ] = $scopes;
    
    return [ 
      T_EXP_FIELD, $table, $field,
      $this->getFieldType( $instance, $field ),
      $this->getFieldMethods( $contexts )
    ];
  }

  private function getFieldByUnary(
    string $parent,
    array $contexts = []
  ): array {
    $expValue = [ 
      T_EXP_VALUE, [
        [ 
          T_STRING,
          $parent === T_EXP_DENYING ? 0 : 1,
          token_name(T_STRING)
        ]
      ]
    ];

    return [ $contexts, [ 
      T_EXP_EQUAL, '=='
    ], $expValue ];
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

  private function equalAdjusted(
    array $contexts = []
  ): array {
    [ $type, $contexts ] = $contexts;
    [ $_, $equal ] = $contexts;
    return [ $type, $equal ];
  }

  private function getEqual(
    array $contexts = []
  ): array {
    [ $contexts ] = $contexts;
    return [ T_EXP_EQUAL, $contexts ];
  }  

  private function getValue(
    array $tokens = []
  ): array {
    return [ T_EXP_VALUE, $tokens ];
  }  
  
  private function getParseFieldAndValue(
     array $scopes = [],
     array $contexts = []
  ): array {
    [ $left, $equal, $right ] = $contexts;
    [ $left, $equal, $right ] = [ 
      $this->isField( $left, $scopes ) 
        ? $this->getField( $scopes, $left )
        : $this->getValue( $left ),
          $this->getEqual( $equal ),
      $this->isField( $right, $scopes ) 
        ? $this->getField( $scopes, $right ) 
        : $this->getValue( $right )
    ];

    if($left[0] === T_EXP_VALUE){
      $equal = $this->equalReverse( $equal );
      $equal = $this->equalAdjusted( $equal );
      return [ $right, $equal, $left ];
    } else {
      $equal = $this->equalAdjusted( $equal );
      return [$left, $equal, $right];
    };
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

    $tokens = $this->getGroupByLogicals($tokens);
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
    $subQueryMethod = array_slice(
      $tokens, $this->dec(
        $this->indexOf(
          $tokens, T_FN), 1
      ), 1
    );

    return $subQueryMethod ?? null;
  }  

  private function isSubQuery(
    array $contexts = []    
  ): bool {
    [ $subQueryMethod ] = $this->getSubQueryMethod($contexts);
    return in_array( $subQueryMethod[1], [ 'any' ]);
  }
  
  private function isLogical(
    array $tokens = []
  ): bool {
    [ $tokens ] = $tokens;
    [ $log ] = $tokens;
    return in_array( $log, [
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

  private function getParserImplode(
    array $contexts = []
  ): array {
    if( count( $contexts ) <= 2){
      return $contexts;
    }

    for( $i = 0; $i < count( $contexts ); $i++ ){
      [ $compareTypeI ] = $contexts[ $i ];
      if( $compareTypeI !== T_EXP_COMPARE ){
        continue;
      }

      [ $compareTypeI, $parentI, $contextsI ] = $contexts[ $i ];
      [ $contextsALeftI, $_, $contextsCLeftI ] = $contextsI;      

      for($j = $i + 1; $j < count($contexts); $j++){
        [ $compareTypeJ ] = $contexts[$j];
        if( $compareTypeJ !== T_EXP_COMPARE ){
          continue;
        }

        [ $_, $contextsLogPrev ] = $contexts[ $j - 1 ];
        [ $contextsALeftJ, $_, $contextsCLeftJ ] = $contexts[$j][2];
        
        if($contextsALeftI[0] === T_EXP_FIELD){
          if($contextsALeftJ[0] === T_EXP_FIELD){
            if($contextsALeftI[1] === $contextsALeftJ[1]){
              if($contextsALeftI[2] === $contextsALeftJ[2]){
                if($contextsALeftI[3] === $this->getType(Datetime::class)){
                  if($contextsALeftJ[3] === $this->getType(Datetime::class)){
                    $isContextsLogPrev = $contextsLogPrev === 'And';
                    if( $isContextsLogPrev ){
                      $contextsALeftI[4] = [
                        [[ "date", [] ]], []
                      ];

                      $contexts[$i] = [ T_EXP_BETWEEN, $parentI, [
                        $contextsALeftI, $contextsCLeftI, $contextsCLeftJ
                      ]];

                      $isContextsLogPrev 
                        ? array_splice($contexts, $j - 1, 2) 
                        : array_splice($contexts, $j, 1);
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    return $contexts;
  }

  private function getExpType(
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

  private function createExpTypeDenying(
    string $parent,
     array $scopes = [],
     array $contexts = []
  ): array {
    $contexts = $this->getGroupByLogicals( array_slice( $contexts, 1 ));
    $contexts = $this->getParser( T_EXP_DENYING, $scopes, $contexts );
    $contexts = [ T_EXP_DENYING, $parent, $contexts ];
    return $contexts;
  }

  private function createExpTypeGroup(
    string $parent,
     array $scopes = [],
     array $contexts = []
  ): array {
    $contexts = $this->getGroupByLogicals( array_slice( $contexts, 1, -1 ));
    $contexts = $this->getParser( T_EXP_GROUP, $scopes, $contexts );
    $contexts = $this->getParserImplode($contexts);
    $contexts = [ T_EXP_GROUP, $parent, $contexts ];    
    return $contexts;
  }
  
  private function createExpTypeSubQuery(
    string $parent,
     array $scopes = [],
     array $contexts = []
  ): array {
    [ $method ] = $this->getSubQueryMethod( $contexts );
    $contexts = $this->getContext( $contexts );
    $scopes = $this->getScopesByContext( $contexts, [], $scopes );
    $contexts = $this->getTokensByContext( $contexts );
    $contexts = $this->getParser( T_EXP_SUBQUERY, $scopes, $contexts );
    $contexts = $this->getParserImplode( $contexts );
    $contexts = [ T_EXP_SUBQUERY, $parent, $contexts, $method[ 1 ], $scopes];    
    return $contexts;
  }

  private function getLogical(
    array $contexts = []
  ): string {
    [ $type ] = $contexts;
    return match( $type ){
      T_BOOLEAN_AND, T_LOGICAL_AND => 'And',
      T_BOOLEAN_OR, T_LOGICAL_OR => 'Or',
        default => ''
    };
  }  
  
  private function createExpTypeLogical(
     array $contexts = []
  ): array {
    [ $contexts ] = $contexts;
    return [ T_EXP_LOGICAL, $this->getLogical( $contexts )];
  }

  private function getVerifyEventsCompare(
    string $parent,
    array $contexts = [],
    array $contextsInGroup = []
  ): array {
    [ $expField, $expEqual ] = $contexts;
    [ $_, $_, $_, $_, $methods ] = $expField;
    [ $_, $compareds ] = $methods;

    if( count( $compareds ) === 0 ){
      return [ T_EXP_COMPARE, $parent, $contexts ];
    } else {
      [ $compareName, $compareArgs ] = $compareds[0];

      /* Transform to Compare Like (%*%, *%, %*) */
      if( in_array( $compareName, ['contains', 'startsWith', 'endsWith'])){
        if( $parent === T_EXP_DENYING ){
          $expEqual[1] = "!=";
        }

        for( $i=0; $i < count($compareArgs); $i++ ){
          if( $i >= 1 ){
            $contextsInGroup[] = $this->createExpTypeLogical([
              [ T_LOGICAL_OR, 'Or', token_name( T_LOGICAL_OR )]
            ]);
          }

          $contextsInGroup[] = [
            T_EXP_COMPARE, T_EXP_GROUP, [
              $expField, $expEqual,
              [ T_EXP_VALUE, match($compareName){
                'contains' => array_merge(
                  [[ T_STRING, '%', token_name( T_STRING )]], $compareArgs[$i],
                  [[ T_STRING, '%', token_name( T_STRING )]]
                ),
                'startsWith' => array_merge(
                  $compareArgs[$i], [[ T_STRING, '%', token_name( T_STRING )]]
                ),
                'endsWith' => array_merge(
                  [[ T_STRING, '%', token_name( T_STRING )]], $compareArgs[$i]
                )
              }]
            ]
          ];
        }

        return [ T_EXP_GROUP, $parent, $contextsInGroup ];
      } else {
        return [ T_EXP_COMPARE, $parent, $contexts ];
      }
    }
  }

  private function createExpTypeCompare(
    string $parent,
     array $scopes = [],
     array $contexts = [],
  ): array {
    $contexts = $this->getGroupByTypes( $contexts );
    $contexts = $this->getParseFieldAndValue( $scopes, $contexts );
    $contexts = $this->getVerifyEventsCompare( $parent, $contexts );
    return $contexts;
  }

  private function createExpTypeUnary(
    string $parent,
     array $scopes = [],
     array $contexts = []
  ): array {
    $contexts = $this->getField( $scopes, $contexts );
    $contexts = $this->getFieldByUnary( $parent, $contexts );
    $contexts = $this->getVerifyEventsCompare( $parent, $contexts );
    return $contexts;
  }  
  
  private function getParser(
    string $parent,
    array $scopes = [],   
    array $contexts = [],
  ): array {
    foreach( $contexts as $i => $tokens ){
      $contexts[ $i ] = match( $this->getExpType( $tokens )){
        T_EXP_DENYING => $this->createExpTypeDenying( $parent, $scopes, $tokens ),
        T_EXP_GROUP => $this->createExpTypeGroup( $parent, $scopes, $tokens ),
        T_EXP_SUBQUERY => $this->createExpTypeSubQuery( $parent, $scopes, $tokens ),
        T_EXP_LOGICAL => $this->createExpTypeLogical( $tokens ),
        T_EXP_COMPARE => $this->createExpTypeCompare( $parent, $scopes, $tokens ),
        T_EXP_UNARY => $this->createExpTypeUnary( $parent, $scopes, $tokens )
      };
    }

    return $contexts;
  }

  private function isPossibleToCache(
  ): bool {
    return isset($this->cacheClassKey)
        && isset($this->cacheMethodKey);
  } 

  private function getCache(
  ): string {
    $cacheClassKey = md5($this->cacheClassKey);
    $cacheMethodKey = md5($this->cacheMethodKey);
    return "orm-where-$cacheClassKey-$cacheMethodKey";
  }

  private function getBuildClear(
  ): void {
    unset($this->reflectionFunction);
    unset($this->expressionType);
    unset($this->contexts);
    unset($this->statics);
    unset($this->closure);
  }

  private function getBuildsDirect(
  ): void {
    $this->scopes = $this->getScopesByContext($this->contexts);
    $this->tokens = $this->getTokensByContext($this->contexts);
    $this->tokens = $this->getParserImplode(
      $this->getParser(
        T_EXP_INITIAL, 
        $this->scopes,
        $this->tokens,
      )
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

  private function getBuilds(
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

  private function startupVariable(
    array $contexts,
    array $statics
  ): array {
    [ $_, $contexts ] = $contexts;

    for($i=0; $i < count($contexts); $i++){
      [ $_, $value ] = $contexts[$i];
      
      if( is_array( $statics )){
        $staticValue = $statics[
          trim($value, '$')
        ] ?? null;
      } else
      if( is_object( $statics )){
        $staticValue = $statics->{
          trim($value, '$')
        } ?? null;
      }
      
      if( $staticValue !== null ){
        if( is_string( $staticValue )){
          $contexts[$i] = [
            T_STRING, 
            $staticValue,
            token_name(T_STRING)
          ];
        } else
        if( is_object( $staticValue )){
          $statics = $staticValue;
          array_splice( $contexts, $i, 2 ); $i--;
        } else
        if( is_array( $staticValue )){
          $statics = $staticValue;
          $tokensOuts = array_splice($contexts, $i, 4);
          array_splice( $contexts, $i, 0, [ $tokensOuts[ 2 ]]); $i--;
        }
      }
    }

    return $contexts;
  }

  public function isEnumValueWithProperty(
    array $contexts
  ): bool {
    if(count($contexts) < 5){
      return false;
    }

    if(count($contexts) === 5){
      [ $enum, $double, $case, $operator, $property ] = $contexts;
        return $enum[0] === T_STRING 
            && $double[0] === T_DOUBLE_COLON 
            && $case[0] === T_STRING 
            && $operator[0] === T_OBJECT_OPERATOR 
            && $property[0] === T_STRING;
    }

    return false;
  }
  
  public function isEnumValueNotProperty(
    array $contexts
  ): bool {
    if(count($contexts) < 3){
      return false;
    }

    if( count($contexts) === 3 ){
      [ $enum, $double, $case ] = $contexts;
        return $enum[0] === T_STRING 
            && $double[0] === T_DOUBLE_COLON 
            && $case[0] === T_STRING;
    }

    return false;
  }
  
  private function updateEnumValue(
    array $contexts,
     bool $isWithProps
  ): array {
    if( $isWithProps ){
      [ $enum, $_, $case, $_, $property ] = $contexts;
    } else {
      [ $enum, $_, $case ] = $contexts;
    }

    $useEnum = array_values(
      array_filter(
        $this->uses, fn(array $use) => $use[1] === $enum[1]
      )
    );

    if( $useEnum ){
      [ $use ] = $useEnum;

      $constantEnum = sprintf( "%s::%s", $use[0], $case[1]);
      if( defined( $constantEnum )){
        $enumCase = constant( $constantEnum );
        if( isset( $property )){
          return [
            T_STRING,
            $property[0] === T_STRING && $property[1] === 'name'
              ? $enumCase->name : $enumCase->value, token_name(T_STRING)
          ];
        } else {
          return [
            T_STRING, 
            $enumCase->value,
            token_name(T_STRING)
          ];
        }
      }
    }

    return [ T_STRING, implode(
      '', array_map( fn(array $context) => $context[1], $contexts )
      ), token_name( T_STRING )
    ];
  }

  private function startupEnums(
    array $contexts
  ): array {
    for($i=0; $i < count($contexts); $i++){
      $enumWithPropertys = array_slice($contexts, $i, 5);
      $enumNotPropertys = array_slice($contexts, $i, 3);

      $isEnumWithPropertys = $this->isEnumValueWithProperty($enumWithPropertys);
      $isEnumNotPropertys = $this->isEnumValueNotProperty($enumNotPropertys);

      if( $isEnumWithPropertys ){
        $contexts[$i] = $this->updateEnumValue(
          $enumWithPropertys, true
        );
      } else if( $isEnumNotPropertys ){
        $contexts[$i] = $this->updateEnumValue(
          $enumNotPropertys, false
        );
      }

      if( $isEnumWithPropertys ){
        array_splice( $contexts, $i + 1, 4 );
      } else if( $isEnumNotPropertys ) {
        array_splice( $contexts, $i + 1, 2 );
      }
    }

    return $contexts;
  }

  private function createParams(
    string $value,
    string $type
  ): string {
    if( strtolower( $value ) === 'null' ){
      return 'Null';
    } else {
      $this->params[] = $this->expressionType->encode($value, $type);
      return '?';
    }
  }

  private function getParams(
    array $contexts,
    string $type
  ): array|string {
    $contextsList = $contexts[0][0] === T_START_BRACKET
                 && $contexts[count($contexts) - 1][0] === T_END_BRACKET;

    if( $contextsList ){
      $contextsListGroups = $this->groupByTypes(
        array_slice($contexts, 1, -1), [
          T_COMMA
        ]
      );

      $contextsListGroups = array_map(
        fn(array $tokens) => array_map(
          fn(array $tokens) => $tokens[1], $tokens
        ), $contextsListGroups
      );

      $contextsListGroups = array_map(
        fn(array $tokens) => trim(
          implode('', $tokens), '"\''
        ), $contextsListGroups
      );

      $contextsListGroups = array_map(
        fn( string $value) => $this->createParams( $value, $type ), $contextsListGroups
      );

      return sprintf( '(%s)', implode(',', $contextsListGroups));
    } else {
      $contexts = array_map( fn(array $token) => $token[1], $contexts );
      return $this->createParams( implode( '', $contexts ), $type );
    };
  }

  private function getParserEqual(
    array $contexts = [],
    array $equal = []
  ): array {
    [ $type, $equal ] = $equal;

    $equal = match( $equal ){
      '===', '==' => '=',
      '!==', '!=' => '<>',
        default => $equal
    };

    $contexts = implode( '', array_map(
      fn(array $token) => $token[1], $contexts
    ));

    $isModeLike = str_contains( $contexts, '%' );
    $siModeNull = strtolower( $contexts ) === 'null';
    $isModeList = str_starts_with( $contexts, '[' )
               && str_ends_with( $contexts, ']' );

    if( $equal === '=' && $isModeLike ){
      $equal = 'Like';
    } else if( $equal === '<>' && $isModeLike ){
      $equal = 'Not Like';
    } else if( $equal === '=' && $isModeList ){
      $equal = 'In';
    } else if( $equal === '<>' && $isModeList ){
      $equal = 'Not In';
    } else if( $equal === '=' && $siModeNull ){
      $equal = 'Is';
    } else if( $equal === '<>' && $siModeNull ){
      $equal = 'Is Not';
    }

    return [ $type, $equal ];
  }

  private function getParserValuesApply(
    string $type,
    array $contexts = []
  ): array {
    if( $type === T_EXP_BETWEEN ){
      [ $field, $valueA, $valueB ] = $contexts;
      [ $_, $_, $_, $type ] = $field; 
      
      $valueA = $this->startupVariable( $valueA, $this->statics );
      $valueB = $this->startupVariable( $valueB, $this->statics );

      return [ 
        $field, [ 
          T_EXP_VALUE, $this->getParams( $valueA, $type)
        ], [ T_EXP_VALUE, $this->getParams( $valueB, $type )]
      ];
    } else
    if( $type === T_EXP_COMPARE ){
      [ $fieldOrValueA, $equal, $fieldOrValueB ] = $contexts;
      if( $fieldOrValueA[0] === T_EXP_VALUE ){
        [ $_, $_, $_, $type ] = $fieldOrValueB;
        $fieldOrValueA = $this->startupVariable( $fieldOrValueA, $this->statics );
        $fieldOrValueA = $this->startupEnums( $fieldOrValueA );        
        return [ $fieldOrValueA, $this->getParserEqual( $fieldOrValueA, $equal ), $this->getParams( $fieldOrValueA, $type )];
      } else 
      if( $fieldOrValueB[0] === T_EXP_VALUE ){
        [ $_, $_, $_, $type ] = $fieldOrValueA;
        $fieldOrValueB = $this->startupVariable( $fieldOrValueB, $this->statics );
        $fieldOrValueB = $this->startupEnums( $fieldOrValueB );
        return [ $fieldOrValueA, $this->getParserEqual( $fieldOrValueB, $equal ), $this->getParams( $fieldOrValueB, $type )];
      } else return [ $fieldOrValueA, $equal, $fieldOrValueB ];      
    }

    return [];
  }   
  
  private function getParserValues(
    array $contexts = []
  ): array {
    if( isset( $this->expressionType ) === false ){
      $this->expressionType = new ExpressionType();
    }

    foreach( $contexts as $i => $tokens ){
      [ $type ] = $tokens;
      if( $type === T_EXP_LOGICAL ){
        continue;
      }
      
      [ $type, $_, $tokens ] = $tokens;
      $contexts[ $i ][ 2 ] = match( $type ){
        T_EXP_COMPARE => $this->getParserValuesApply( T_EXP_COMPARE, $tokens ),
        T_EXP_BETWEEN => $this->getParserValuesApply( T_EXP_BETWEEN, $tokens ),
          default => $this->getParserValues( $tokens )
      };
    }

    return $contexts;
  }

  private function getParseWheresGroup(
    array $contexts = []    
  ): string {
    [ $_, $_, $contexts ] = $contexts;
    return "({$this->getParseWheres($contexts)})";
  }

  private function getParseWheresDenying(
    array $contexts = []    
  ): string {
    [ $_, $_, $contexts ] = $contexts;
    [ $tokens ] = $contexts;
    
    return $tokens[0] === T_EXP_SUBQUERY
      ? "Not {$this->getParseWheres($contexts)}"
      : "{$this->getParseWheres($contexts)}";
  }

  private function getTableFromScope(
    array $contexts = [] 
  ): string {
    [ $scope ] = array_reverse( $contexts );
    [ $_, $table ] = $scope;
    return $table;
  }

  private function getEventFromMethod(
    string $event 
  ): string {
    return [
      'any' => 'Exists'
    ][$event];
  }  
  
  private function getParseWheresSubQuery(
    array $contexts = []    
  ): string {
    [ $_, $_, $contexts, $method, $scopes ] = $contexts;
    return sprintf( "%s ( Select 1 From %s Where %s)", 
      $this->getEventFromMethod( $method ), 
      $this->getTableFromScope( $scopes ), 
      $this->getParseWheres( $contexts )
    );
  }

  private function getMethodModify(
    array $contexts
  ): string {
    [ $_, $table, $field, $_, $methods ] = $contexts;
    [ $modifys ] = $methods;

    $driver = DB::driver();
    $target = "{$table}.{$field}";

    foreach( $modifys as $method ){
      if( $method[0] === "date" ){
        $target = $driver !== "mysql"
          ? "Cast({$target} As Date)" 
          : "Date({$target})";
      } else
      if( $method[0] === "upper" ){
        $target = "Upper({$target})";
      } else
      if( $method[0] === "lower" ){
        $target = "Lower({$target})";
      } else
      if( $method[0] === "trim" ){
        $target = "Trim({$target})";
      }
    }

    return $target;
  }

  private function getParseWheresBetween(
    array $contexts = []    
  ): string {
    [ $_, $_, $contexts ] = $contexts;
    [ $contexts, $valueStart, $valueEnd ] = $contexts;
    return sprintf( "%s BetWeen %s And %s", $this->getMethodModify($contexts), $valueStart[1], $valueEnd[1] );
  }  
  
  private function getParseWheresCompare(
    array $contexts = []    
  ): string {
    [ $_, $_, $contexts ] = $contexts;
    [ $contextsA, $equal, $contextsB ] = $contexts;

    if( is_array( $contextsA ) && is_array( $contextsB )){
      [ $contextsA, $equal, $contextsB ] = [
        $this->getMethodModify( $contextsA ), $equal[1], 
        $this->getMethodModify( $contextsB )
      ];
    } else if( is_array( $contextsA ) && is_string( $contextsB )){
      [ $contextsA, $equal, $contextsB ] = [
        $this->getMethodModify( $contextsA ), $equal[1], $contextsB
      ];
    } else if( is_string( $contextsA ) && is_array( $contextsB )){
      [ $contextsA, $equal, $contextsB ] = [
        $contextsA, $equal[1], $this->getMethodModify( $contextsB )
      ];
    }

    return "{$contextsA} {$equal} {$contextsB}";
  }

  private function getParseWheresLogical(
    array $contexts = []    
  ): string {
    [ $_, $contexts ] = $contexts;
    return "{$contexts}";
  }  

  private function getParseWheres(
    array $contexts = []
  ): string {
    foreach($contexts as $i => $tokens){
      [ $type ] = $tokens;
      $contexts[ $i ] = match( $type ){
        T_EXP_GROUP => $this->getParseWheresGroup( $tokens ),
        T_EXP_DENYING => $this->getParseWheresDenying( $tokens ),
        T_EXP_SUBQUERY => $this->getParseWheresSubQuery( $tokens ),
        T_EXP_BETWEEN => $this->getParseWheresBetween( $tokens ),
        T_EXP_COMPARE => $this->getParseWheresCompare( $tokens ),
        T_EXP_LOGICAL => $this->getParseWheresLogical( $tokens )
      };
    }

    return implode( " ", $contexts );
  }

  private function getScriptTable(
  ): string {
    return $this->getTableFromScope(
      $this->scopes
    );
  }  

  private function startups(
  ): void {
    $this->getFileRows();
    $this->getStatics();
    $this->getCacheKey();
    $this->getUsesRows();
    $this->getContexts();
    $this->getBuilds();
  }

  public function get(
  ): array {
    $this->startups();
    return [
      $this->getScriptTable(),
      $this->getParseWheres(
        $this->tokens
      ), $this->params
    ];
  }
}