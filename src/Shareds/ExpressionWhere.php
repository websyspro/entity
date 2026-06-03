<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use ReflectionFunction;
use function count, array_slice, is_string, in_array, ord, is_array, is_object, sprintf, defined;
use Websyspro\Entity\Decorations\Columns\Datetime;

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
  
  private function getField(
    array $scopes = [],
    array $tokens = []    
  ): array {
    [ $variable, $field ] = [
      $tokens[0][1], 
      $tokens[2][1]
    ];

    $scopes = array_values(
      array_filter( $scopes, 
        fn(array $scope) => $scope[2] === $variable
      )
    );

    if(count( $scopes ) !== 0){
      [ $instance, $table ] = $scopes[0];
      $metadata = new EntityStructure( $instance );
      $metadata = $metadata->get();

      return [ T_EXP_FIELD, $table, $field, 
        $this->getType( $metadata->contexts['types'][$field] )
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
      $this->isField($left, $scopes) 
        ? $this->getField($scopes, $left) 
        : $this->getValue($left),
          $this->getEqual($equal),
      $this->isField($right, $scopes) 
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

  private function getParserImplode(
    array $tokens = []
  ): array {
    if(count($tokens) <= 2){
      return $tokens;
    }

    for($i = 0; $i < count($tokens); $i++){
      [ $compoareI, $parentI, $scopesI 
      ] = $tokens[$i];

      if($compoareI !== T_EXP_COMPARE){
        continue;
      }

      [ $tokenALeftI, $_, $tokenCLeftI 
      ] = $tokens[$i][3];      

      for($j = $i + 1; $j < count($tokens); $j++){
        [ $compoareJ ] = $tokens[$j];

        if($compoareJ !== T_EXP_COMPARE){
          continue;
        }

        [ $tokenLogPrev ] = $tokens[$j - 1][2];
        [ $tokenALeftJ, $_, $tokenCLeftJ 
        ] = $tokens[$j][3];

        if($tokenALeftI[0] === T_EXP_FIELD){
          if($tokenALeftJ[0] === T_EXP_FIELD){
            if($tokenALeftI[1] === $tokenALeftJ[1]){
              if($tokenALeftI[2] === $tokenALeftJ[2]){
                if($tokenALeftI[3] === $this->getType(Datetime::class)){
                  if($tokenALeftJ[3] === $this->getType(Datetime::class)){
                    $isTokenLogPrev = in_array(
                      $tokenLogPrev, [ 
                        T_LOGICAL_AND,
                        T_BOOLEAN_AND
                      ]
                    );

                    if( $isTokenLogPrev ){
                      $tokens[$i] = [
                        T_EXP_BETWEEN,
                        $parentI,
                        $scopesI, [
                          $tokenALeftI,
                          $tokenCLeftI,
                          $tokenCLeftJ
                        ]
                      ];

                      $isTokenLogPrev 
                        ? array_splice($tokens, $j - 1, 2) 
                        : array_splice($tokens, $j, 1);
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    return $tokens;
  }
  
  private function getParser(
    string $parent,
     array $contexts = [],
     array $scopes = [],    
  ): array {
    foreach($contexts as $i => $tokens){
      if($this->isDenying($tokens)){
        $tokens = $this->getGroupByLogicals(array_slice( $tokens, 1 ));
        $tokens = $this->getParser(T_EXP_DENYING, $tokens, $scopes);
        $contexts[$i] = [T_EXP_DENYING, $parent, $scopes, $tokens];
      } else if($this->isGroup($tokens)){
        $tokens = $this->getGroupByLogicals(array_slice( $tokens, 1, -1 ));
        $tokens = $this->getParser(T_EXP_GROUP, $tokens, $scopes);
        $tokens = $this->getParserImplode($tokens);
        $contexts[$i] = [T_EXP_GROUP, $parent, $scopes, $tokens];
      } else if($this->isSubQuery($tokens)){
        [ $_ ,$method ] = $this->getSubQueryMethod($tokens);
        $tokens = $this->getContext($tokens);
        $scopes = $this->getScopesByContext($tokens, [], $scopes);
        $tokens = $this->getTokensByContext($tokens);
        $tokens = $this->getParser(T_EXP_SUBQUERY, $tokens, $scopes);
        $tokens = $this->getParserImplode($tokens);
        $contexts[$i] = [T_EXP_SUBQUERY, $parent, $method, $scopes, $tokens];
      } else if($this->isLogical($tokens)){
        [ $token ] = $tokens;
        $contexts[$i] = [T_EXP_LOGICAL, $parent, $token];
      } else if($this->isCompare($tokens)){
        $tokens = $this->getGroupByTypes($tokens);
        $tokens = $this->getParseFieldAndValue( $scopes, $tokens );
        $contexts[$i] = [T_EXP_COMPARE, $parent, $scopes, $tokens];
      } else if($this->isUnary( $tokens )){
        $tokens = $this->getField($scopes, $tokens);
        $contexts[$i] = [T_EXP_UNARY, $parent, $scopes, $tokens];
      } 
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
    return sprintf(
      'orm-where-%s-%s', 
      md5($this->cacheClassKey),
      md5($this->cacheMethodKey)
    );
  }

  private function getBuildClear(
  ): void {
    unset($this->reflectionFunction);
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
        $this->tokens,
        $this->scopes
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
        if( $hash === md5(serialize($this->contexts))){
          $this->scopes = $context['scopes'];
          $this->tokens = $this->getParserValues($context['tokens']);
          $this->getBuildClear();
        } else $this->getBuildsDirect();
      } else $this->getBuildsDirect();
    } else $this->getBuildsDirect();
  }

  private function startupVariable(
    array $contexts,
    array $statics
  ): array {
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
    string $value
  ): string {
    $this->params[] = $value;
    return '?';
  }

  private function startupParams(
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
        fn( string $value) => $this->createParams(
          //$type::$columnType->Encode($value)
          $value
        ), $contextsListGroups
      );

      return sprintf( '(%s)', implode(',', $contextsListGroups));
    } else {
      [ $value ] = $contexts;
      return $this->createParams(
        //$type::$columnType->Encode( $value[1])
        $value[1]
      );
    };
  }
  
  private function startupTypes(
    array $contexts
  ): array {
    [ $expType, $parentType, $scopes, $tokens 
    ] = $contexts;

    if( $expType === T_EXP_UNARY ){
      return [ 
        $expType,
        $parentType,
        $scopes, [
          $tokens,
          $this->createToken('=='),
          [ T_STRING, 0, token_name( T_STRING )]
        ]
      ];
    } else
    if( $expType === T_EXP_BETWEEN ){
      [ $tokenA, $tokenB, $tokenC 
      ] = $tokens;

      $tokenB[1] = $this->startupVariable($tokenB[1], $this->statics);
      $tokenC[1] = $this->startupVariable($tokenC[1], $this->statics);
      $tokenB[1] = $this->startupParams($tokenB[1], $tokenA[3]);
      $tokenC[1] = $this->startupParams($tokenC[1], $tokenA[3]);      
      return [ $tokenA, $tokenB, $tokenC ];
    } else
    if( $expType === T_EXP_COMPARE ){
      [ $tokenA, $tokenB, $tokenC 
      ] = $tokens;

      if( $tokenA[0] === T_EXP_FIELD ){
        if( $tokenC[0] === T_EXP_VALUE ){
          $tokenC[1] = $this->startupVariable($tokenC[1], $this->statics);
          $tokenC[1] = $this->startupEnums($tokenC[1]);
          $tokenC[1] = $this->startupParams($tokenC[1], $tokenA[3]);
        }
      } else
      if( $tokenA[0] === T_EXP_VALUE ){
        if( $tokenC[0] === T_EXP_FIELD ){
          $tokenA[1] = $this->startupVariable($tokenA[1], $this->statics);
          $tokenA[1] = $this->startupEnums($tokenA[1]);
          $tokenA[1] = $this->startupParams($tokenA[1], $tokenC[3]);
        }
      }

      return [ $tokenA, $tokenB, $tokenC ];
    }

    return $tokens;
  }   
  
  private function getParserValues(
    array $contexts = []
  ): array {
    foreach($contexts as $i => $tokens){
      [ $expType ] = $tokens;

      if($expType === T_EXP_DENYING){
        $contexts[$i][3] = $this->getParserValues($tokens[3]);
      } else
      if($expType === T_EXP_GROUP){
        $contexts[$i][3] = $this->getParserValues($tokens[3]);
      } else
      if($expType === T_EXP_SUBQUERY){
        $contexts[$i][4] = $this->getParserValues($tokens[4]);
      } else
      if($expType === T_EXP_UNARY){
        $contexts[$i][3] = $this->startupTypes($tokens);
      } else
      if($expType === T_EXP_COMPARE){
        $contexts[$i][3] = $this->startupTypes($tokens);
      } else
      if($expType === T_EXP_BETWEEN){
        $contexts[$i][3] = $this->startupTypes($tokens);
      }
    }

    return $contexts;
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
  ): mixed {
    $this->startups();
    return $this;
  }
}