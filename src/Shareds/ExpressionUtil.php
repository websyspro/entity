<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Decorations\Columns\Datetime;
use ReflectionFunction;
use Closure;
use function ord, count, is_string, is_array, in_array, array_slice, sprintf, defined;
use Websyspro\Entity\Enums\MetaType;

define( "T_START_PARENTESES", 40 );
define( "T_END_PARENTESES", 41 );
define( "T_START_BRACKET", 91 );
define( "T_END_BRACKET", 93 );
define( "T_START_BRACE", 123 );
define( "T_END_BRACE", 125 );
define( "T_DOT", 46 );
define( "T_COMMA", 44 );
define( "T_SEMICOLON", 59 );
define( "T_COLON", 58 );
define( "T_QUESTION", 63 );
define( "T_PLUS", 43 );
define( "T_MINUS", 45 );
define( "T_MULTIPLY", 42 );
define( "T_DIVIDE", 47 );
define( "T_EQUAL", 61 );
define( "T_GREATER_THAN", 62 );
define( "T_LESS_THAN", 60 );
define( "T_NOT", 33 );

define( "T_EXPRESSION_NODE", "ExpressionNode" );
define( "T_EXPRESSION_GROUP", "ExpressionGroup" );
define( "T_EXPRESSION_UNARY", "ExpressionUnary" );
define( "T_EXPRESSION_LOGICAL", "ExpressionLogical" );
define( "T_EXPRESSION_SUBQUERY", "ExpressionSubQuery" );
define( "T_EXPRESSION_COMPARE", "ExpressionCompare" );
define( "T_EXPRESSION_BETWEEN", "ExpressionBetween" );
define( "T_EXPRESSION_LIKE", "ExpressionLike" );
define( "T_EXPRESSION_NOT_LIKE", "ExpressionNotLike" );
define( "T_EXPRESSION_IN", "ExpressionIn" );
define( "T_EXPRESSION_NOT_IN", "ExpressionNotIn" );
define( "T_EXPRESSION_FIELD", "ExpressionField" );
define( "T_EXPRESSION_VALUE", "ExpressionValue" );
define( "T_EXPRESSION_EQUAL", "ExpressionEqual" );
define( "T_EXPRESSION_NEGATIVE", "ExpressionNegative" );

define( "T_IS_EXPRESSION_EQUAL_IN", "In" );
define( "T_IS_EXPRESSION_EQUAL_NOT_IN", "Not In" );
define( "T_IS_EXPRESSION_EQUAL_LIKE", "Like" );
define( "T_IS_EXPRESSION_EQUAL_NOT_LIKE", "Not Like" );

define( "T_IS_EXPRESSION_UNARY_IS_NOT_YES", "0" );
define( "T_IS_EXPRESSION_UNARY_IS_NOT_NO", "1" );

define( "T_IS_EXPRESSION_NEGATIVE", 1 );
define( "T_IS_EXPRESSION_GROUP", 2 );
define( "T_IS_EXPRESSION_SUBQUERY", 3 );
define( "T_IS_EXPRESSION_LOGICAL", 4 );
define( "T_IS_EXPRESSION_UNARY", 5 );
define( "T_IS_EXPRESSION_COMPARE", 6 );
define( "T_IS_EXPRESSION_FIELD", 7 );
define( "T_IS_EXPRESSION_EQUAL", 8 );
define( "T_IS_EXPRESSION_VALUE", 9 );

define( "T_FIELD_AND_FIELD", 1 );
define( "T_FIELD_AND_VALUE", 2 );
define( "T_VALUE_AND_VALUE", 3 );
define( "T_VALUE_AND_FIELD", 4 ); 

define( "T_KEY_OBJECT", "object" );
define( "T_KEY_TOKENS", "tokens" );
define( "T_KEY_METHODS", "methods" );
define( "T_KEY_ACTION", "action" );
define( "T_KEY_IS_NOT", "isnot" );
define( "T_KEY_ARGS", "args" );
define( "T_KEY_ENTITY", "entity" );
define( "T_KEY_COLUMNS", "columns" );
define( "T_KEY_COLUMNS_ALIAS", "columnsAlias" );
define( "T_KEY_FIELD", "field" );
define( "T_KEY_ALIAS", "alias" );
define( "T_KEY_ISLIST", "islist" );
define( "T_KEY_TYPE", "type" );
define( "T_KEY_VALUE", "value" );
define( "T_KEY_NUMBER", "number" );
define( "T_KEY_NAME", "name" );
define( "T_KEY_VARIABLE", "variable" );
define( "T_KEY_INSTANCE", "instance" );
define( "T_KEY_CACHE", "cache" );
define( "T_KEY_CACHE_TOKENS", "cache-tokens" );

define( "T_KEY_NO", "no" );
define( "T_KEY_YES", "yes" );

define( "T_METHODS_ACTION_MODIFY_LIST", [ "trim", "upper", "lower" ]);
define( "T_METHODS_ACTION_MODIFY", "modify" );
define( "T_METHODS_ACTION_COMPARE", "compare" );

define( "T_METHODS_START_WITH", "startWith" );
define( "T_METHODS_NOT_START_WITH", "notStartWith" );
define( "T_METHODS_END_WITH", "endtWith" );
define( "T_METHODS_NOT_END_WITH", "notEndtWith" );
define( "T_METHODS_CONTAINS", "contains" );
define( "T_METHODS_NOT_CONTAINS", "notContains" );

define( "T_EVENTS_LIST", [ "any" => "Exists" ]);

class ExpressionUtil
{
  public string $cacheOrm;

  public function find(
    array $tokens,
    int $number
  ): int {
    foreach( $tokens as $key => $token ){
      if( isset( $token[ T_KEY_NUMBER ])){
        if( (int)$token[ T_KEY_NUMBER ] === $number ){
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
      if( $token[T_KEY_NUMBER] === T_EQUAL ) return $key;
      if( $token[T_KEY_NUMBER] === T_IS_EQUAL ) return $key;
      if( $token[T_KEY_NUMBER] === T_IS_IDENTICAL ) return $key;
      if( $token[T_KEY_NUMBER] === T_IS_NOT_EQUAL ) return $key;
      if( $token[T_KEY_NUMBER] === T_IS_NOT_IDENTICAL ) return $key;
      if( $token[T_KEY_NUMBER] === T_IS_GREATER_OR_EQUAL ) return $key;
      if( $token[T_KEY_NUMBER] === T_IS_SMALLER_OR_EQUAL ) return $key;
      if( $token[T_KEY_NUMBER] === T_GREATER_THAN ) return $key;
      if( $token[T_KEY_NUMBER] === T_LESS_THAN ) return $key;
    }

    return -1;
  }  

  public function startParentese(
    array $token
  ): bool {
    if(isset($token[T_KEY_NUMBER]) === false){
      return false;
    }

    return $token[T_KEY_NUMBER] === T_START_PARENTESES;
  }

  public function endParentese(
    array $token
  ): bool {
    if( isset( $token[T_KEY_NUMBER]) === false){
      return false;
    }

    return $token[T_KEY_NUMBER] === T_END_PARENTESES;
  } 

  public function findNegative(
    array $token
  ): int {
    if( isset( $token[T_KEY_NUMBER]) === false){
      return false;
    }

    return $token[T_KEY_NUMBER] === T_NOT;
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

    [$variable, $objectOperator, $string] = $tokens;
    return $variable[T_KEY_NUMBER] === T_VARIABLE 
        && $objectOperator[T_KEY_NUMBER] === T_OBJECT_OPERATOR 
        && $string[T_KEY_NUMBER] === T_STRING;
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

  public function getExpressionType(
    array $tokens
  ): int {
    if( $this->isExpressionNegative( $tokens )){
      return T_IS_EXPRESSION_NEGATIVE;
    } else if( $this->isExpressionGroup( $tokens )){
      return T_IS_EXPRESSION_GROUP;
    } else if( $this->isExpressionSubQuery( $tokens )){
      return T_IS_EXPRESSION_SUBQUERY;
    } else if( $this->isExpressionLogical( $tokens )){
      return T_IS_EXPRESSION_LOGICAL;
    } else if( $this->isExpressionUnary( $tokens )){
      return T_IS_EXPRESSION_UNARY;
    } else if( $this->isExpressionCompare( $tokens )){
      return T_IS_EXPRESSION_COMPARE;
    } else if( $this->isExpressionField( $tokens )){
      return T_IS_EXPRESSION_FIELD;
    } else if( $this->isExpressionEquals( $tokens )){
      return T_IS_EXPRESSION_EQUAL;
    } else if( $this->isExpressionValue( $tokens )){
      return T_IS_EXPRESSION_VALUE;
    }

    return -1;
  }

  public function getExpressionCompareType(
    array $tokens
  ): int {
    if( $this->isExpressionField( $tokens )){
      return T_IS_EXPRESSION_FIELD;
    } else if( $this->isExpressionEquals( $tokens )){
      return T_IS_EXPRESSION_EQUAL;
    } else if( $this->isExpressionValue( $tokens )){
      return T_IS_EXPRESSION_VALUE;
    }

    return -1;
  }  

  public function getEventBySubQuery(
    array $tokens
  ): string {
    [ $tokens ] = array_slice( $tokens, $this->findPrev( $tokens, T_FN ) - 1, 1);
    return T_EVENTS_LIST[ $tokens[ T_KEY_VALUE ]];
  }

  public function existsEvent(
    array $tokens
  ): bool {
    return in_array( $this->getEventBySubQuery( $tokens ), T_EVENTS_LIST );
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
      T_KEY_NUMBER => $number,
      T_KEY_VALUE => $value,
      T_KEY_TYPE => $this->namberToken(
        $number
      )
    ];
  }

  public function createTokenCompareEqualIsNegatiive(
    array $tokens
  ): array {
    return array_merge( $tokens, 
      [ $this->createToken( "===" ) ],
      [ $this->createToken( false )]
    );
  }

  public function createTokenCompareEqualIsNotNegatiive(
    array $tokens
  ): array {
    return array_merge( $tokens, 
      [ $this->createToken( "===" ) ],
      [ $this->createToken( true )]
    );
  }  

  public function dropWriteSpace(
    array $tokens
  ): array {
    for($i=0; $i<count($tokens); $i++){
      if($tokens[$i][T_KEY_NUMBER] === T_WHITESPACE){
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
      if($tokens[$i][T_KEY_NUMBER] === T_START_PARENTESES){
        $parenteses++;
      }

      if($tokens[$i][T_KEY_NUMBER] === T_END_PARENTESES){
        $parenteses--;

        if($parenteses < 0){
          $tokens = array_slice(
            $tokens, 0, $i
          ); break;
        }          
      }

      if($parenteses < 1){
        if($tokens[$i][T_KEY_NUMBER] === T_SEMICOLON){
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
      if( $tokens[$i][T_KEY_NUMBER] === T_START_PARENTESES ){
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
  
  public function cache(
    array $tokens
  ): string {
    if( file_exists( BASEDIR_APP . "/cache" ) === false ){
      mkdir( BASEDIR_APP . "/cache" );
    }

    return sprintf( "%s/cache/cache-orm-%s.php", 
      BASEDIR_APP, $this->cacheOrm = md5( serialize( $tokens ))
    );
  }

  public function existsCache(
    array $tokens
  ): bool {
    return file_exists( $this->cache( $tokens ));
  }
  
  public function saveCache(
    array $cacheTokens,
    array $tokens
  ): bool {
    return file_put_contents( 
      $this->cache($cacheTokens), sprintf(
        "<?php\n\nreturn %s;", var_export( $tokens, true )
      )
    );
  }
  
  public function loadCache(
  ): array {
    return require sprintf( "%s/cache/cache-orm-%s.php", 
      BASEDIR_APP, $this->cacheOrm
    );
  }  
  
  public function tokensAll(
    Closure $closure,
    string $entity,
    array $tokens = [],
    array $tokensContext = []
  ): array {
    $tokens = $this->tokensByReflection(
      ClosureUtil::getReflectFunction(
        $closure
      )
    );

    if( Cache::exist( T_KEY_CACHE_TOKENS, $tokens )){
      return Cache::load( T_KEY_CACHE_TOKENS, $tokens );
    } else {
      for($i=0; $i < count($tokens); $i++){
        $tokensContext[$i] = is_array( $tokens[ $i ]) 
          ? $this->createToken( $tokens[ $i ]) 
          : $this->createToken( $tokens[ $i ]);
      }

      $tokensContext = $this->dropWriteSpace($tokensContext);
      $tokensContext = $this->dropInitialInvalids($tokensContext);
      $tokensContext = $this->dropEndInvalids($tokensContext);
      return Cache::save( T_KEY_CACHE_TOKENS, $tokens, [
        T_KEY_ENTITY => $entity::meta(MetaType::Query)->entity,
        T_KEY_COLUMNS => $entity::meta(MetaType::Query)->columns,
        T_KEY_COLUMNS_ALIAS => $entity::meta(MetaType::Query)->alias, 
        T_KEY_TOKENS => $tokensContext
      ]);
    }
  }  

  public function isLogical(
    array $token
  ): bool {
    if(isset($token[T_KEY_NUMBER]) === false){
      return false;
    }

    return $token[T_KEY_NUMBER] === T_LOGICAL_AND
        || $token[T_KEY_NUMBER] === T_LOGICAL_OR
        || $token[T_KEY_NUMBER] === T_BOOLEAN_AND
        || $token[T_KEY_NUMBER] === T_BOOLEAN_OR;
  }

  public function preCompileParserTokens(
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

  public function adjustCompare(
    array $tokens
  ): string|array {
    if (count( $tokens ) === 0) {
      return end( $tokens );
    }

    [ T_KEY_NUMBER => $number ] = end( $tokens );
    return match($number) {
      T_LOGICAL_AND => 'And', T_BOOLEAN_AND => 'And',
      T_LOGICAL_OR => 'Or', T_BOOLEAN_OR => 'Or'
    };
  }

  public function isEquals(
    array $token
  ): bool {
    if( isset( $token[ T_KEY_NUMBER ]) === false){
      return false;
    }

    return $token[ T_KEY_NUMBER ] === T_EQUAL
        || $token[ T_KEY_NUMBER ] === T_IS_EQUAL
        || $token[ T_KEY_NUMBER ] === T_IS_IDENTICAL
        || $token[ T_KEY_NUMBER ] === T_IS_NOT_EQUAL
        || $token[ T_KEY_NUMBER ] === T_IS_NOT_IDENTICAL
        || $token[ T_KEY_NUMBER ] === T_IS_GREATER_OR_EQUAL
        || $token[ T_KEY_NUMBER ] === T_IS_SMALLER_OR_EQUAL
        || $token[ T_KEY_NUMBER ] === T_GREATER_THAN
        || $token[ T_KEY_NUMBER ] === T_LESS_THAN;
  }

  public function isObjectOperator(
    array $token
  ): bool {
    return isset( $token[ T_KEY_NUMBER ]) 
      ? $token[ T_KEY_NUMBER ] === T_OBJECT_OPERATOR
      : false;
  }  

  public function adjustEqualFieldWithValue(
    array $expressionEqual,
    array $expressionRight
  ): array {
    [ T_KEY_TOKENS => $tokens, T_KEY_ISLIST => $islist ] = $expressionRight;
    [ T_KEY_NUMBER => $number, T_KEY_VALUE => $value 
    ] = $expressionEqual[ T_KEY_TOKENS ];

    $isExpressionNull = strtoupper( $tokens ) === "NULL";
    $isExpressionLike = preg_match( "#%#", preg_replace( "#\\\%#", "", $tokens ));
    $isExpressionEqual = in_array( $number, [ T_EQUAL, T_IS_EQUAL, T_IS_IDENTICAL ]);
    $isNotExpressionEqual = in_array( $number, [ T_IS_NOT_EQUAL, T_IS_NOT_IDENTICAL ]);

    if( $isExpressionEqual && $isExpressionLike ){
      return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_VALUE => 'Like' ];
    } else if( $isNotExpressionEqual && $isExpressionLike ){
      return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_VALUE => 'Not Like' ];
    } else if( $isExpressionEqual && $islist === T_KEY_YES ){
      return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_VALUE => 'In' ];
    } else if( $isNotExpressionEqual && $islist === T_KEY_YES ){
      return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_VALUE => 'Not In' ];
    } else if( $isExpressionEqual && $isExpressionNull ){
      return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_VALUE => 'Is' ];
    } else if( $isNotExpressionEqual && $isExpressionNull ){
      return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_VALUE => 'Is Not' ];
    } else if( $isExpressionEqual ){
      return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_VALUE => '=' ];
    } else if( $isNotExpressionEqual ){
      return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_VALUE => '<>' ];
    } else return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_VALUE => $value ];
  }

  public function adjustEqualFieldWithField(
    array $expressionEqual    
  ): array {
    [ T_KEY_NUMBER => $number, T_KEY_VALUE => $value 
    ] = $expressionEqual[ T_KEY_TOKENS ];

    $isExpressionEqual = in_array( $number, [ T_EQUAL, T_IS_EQUAL, T_IS_IDENTICAL ]);
    $isNotExpressionEqual = in_array( $number, [ T_IS_NOT_EQUAL, T_IS_NOT_IDENTICAL ]);

    if( $isExpressionEqual ){
      return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_VALUE => '=' ];
    } else if( $isNotExpressionEqual ){
      return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_VALUE => '<>' ];
    } else return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_VALUE => $value ];
  }

  public function chuckTokensByNumber(
    array $tokens = [],
      int $number = 0,
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if( $depth === 0 ){
        if( $token[ T_KEY_NUMBER ] === $number ){
          if( $curr ){
            $accu[] = $curr;
            $curr = [];
          }
  
          continue;
        }
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

  public function extractNegativeToken(
    array $tokens = []
  ): array {
    return $this->isExpressionNegative( $tokens )
      ? [ T_KEY_YES, array_slice( $tokens, 1 )] 
      : [ T_KEY_NO, $tokens ];
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

  public function adjustCompareReverse(
    array $expressionEqual
  ): array {
    $value = match( $expressionEqual[ T_KEY_TOKENS ][ T_KEY_VALUE ]){
      '>' => '<', '<' => '>', '>=' => '<=', '<=' => '>='
    };

    $number = match( $expressionEqual[ T_KEY_TOKENS ][ T_KEY_NUMBER ]){
      T_GREATER_THAN => T_LESS_THAN, T_LESS_THAN => T_GREATER_THAN,
      T_IS_GREATER_OR_EQUAL => T_IS_SMALLER_OR_EQUAL, T_IS_SMALLER_OR_EQUAL => T_IS_GREATER_OR_EQUAL,
    };

    return [ T_KEY_OBJECT => T_EXPRESSION_EQUAL, T_KEY_TOKENS => [
      T_KEY_NUMBER => $number, T_KEY_VALUE => $value, T_KEY_TYPE => $this->namberToken( $number )
    ]];
  }

  public function adjustComparePositions(
    array $tokens
  ): array {
    if( count( $tokens ) !== 3 ){
      return $tokens;
    }

    [ $expressionLeft, $expressionEqual, $expressionRight ] = $tokens;
    if( $expressionLeft[ T_KEY_OBJECT ] === T_EXPRESSION_VALUE ){
      if( $expressionRight[ T_KEY_OBJECT ] === T_EXPRESSION_FIELD ){
        $expressionEqual = $this->adjustCompareReverse( $expressionEqual );
        return [ $expressionRight, $expressionEqual, $expressionLeft ];
      }
    }

    return $tokens;
  }

  public function parseValueType(
    Closure $closure,
    array $expressionLeft,
    string $value
  ): string {
    $instanceType = $expressionLeft[
      T_KEY_TYPE
    ];

    if( strtolower( $value )  === 'null' ){
      return $instanceType::$columnType->Encode( $value );
    }

    return ClosureUtil::createParam(
      $closure, $instanceType::$columnType->Encode( $value )
    );
  }

  public function parseValue(
    Closure $closure,
    array $expressionLeft,
    array $expressionRight
  ): array {
    $values = $expressionRight[ T_KEY_ISLIST ] === T_KEY_YES
      ? explode( ",", trim( $expressionRight[ T_KEY_TOKENS ], "[]" )) 
      : [ $expressionRight[ T_KEY_TOKENS ] ];

    // $values = array_map(
    //   fn( string $value ) => $this->parseValueType(
    //     $closure, $expressionLeft, $value
    //   ), $values
    // );

    return [
      T_KEY_OBJECT => T_EXPRESSION_VALUE,
      T_KEY_ISLIST => $expressionRight[ T_KEY_ISLIST ],
      T_KEY_VALUE => $values
      // T_KEY_VALUE => $expressionRight[ T_KEY_ISLIST ] === T_KEY_YES
      //     ? sprintf(  "(%s)", join(", ", $values)) : join( "", $values ) 
    ];
  }

  public function adjustCompareParserValue(
    Closure $closure,
    array $tokens
  ): array {
    if( count( $tokens ) !== 3 ){
      return $tokens;
    }

    [ $expressionLeft, $expressionEqual, $expressionRight ] = $tokens;
    if( $expressionLeft[ T_KEY_OBJECT ] === T_EXPRESSION_FIELD ){
      if( $expressionRight[ T_KEY_OBJECT ] === T_EXPRESSION_VALUE ){
        // $expressionEqual = $this->adjustEqualFieldWithValue( $expressionEqual, $expressionRight );
        // $expressionRight = $this->parseValue( $closure, $expressionLeft, $expressionRight );
        return [ $expressionLeft, $expressionEqual, $expressionRight ];
      }
    }
    if( $expressionLeft[ T_KEY_OBJECT ] === T_EXPRESSION_FIELD ){
      if( $expressionRight[ T_KEY_OBJECT ] === T_EXPRESSION_FIELD ){
        $expressionEqual = $this->adjustEqualFieldWithField( $expressionEqual );
        return [ $expressionLeft, $expressionEqual, $expressionRight ];
      }
    }
    
    return $tokens;
  }

  public function expressionCompareIsField(
    array $tokens
  ): bool {
    return isset( $tokens[ T_KEY_OBJECT ]) 
      ? $tokens[ T_KEY_OBJECT ] === T_EXPRESSION_FIELD 
      : false;    
  }

  public function expressionCompareIsValue(
    array $tokens
  ): bool {
    return isset( $tokens[ T_KEY_OBJECT ]) 
      ? $tokens[ T_KEY_OBJECT ] === T_EXPRESSION_VALUE 
      : false;    
  }  

  public function expressionCompareType(
    array $tokens
  ): int {
    if( count( $tokens[ T_KEY_TOKENS ]) !== 3){
      return 0;
    }

    [ $expressionLeft, $_, $expressionRight
    ] = $tokens[ T_KEY_TOKENS ];

    if( $this->expressionCompareIsField( $expressionLeft )){
      if( $this->expressionCompareIsField( $expressionRight )){
        return T_FIELD_AND_FIELD;
      }
    }
    if( $this->expressionCompareIsField( $expressionLeft )){
      if( $this->expressionCompareIsValue( $expressionRight )){
        return T_FIELD_AND_VALUE;
      }
    }
    if( $this->expressionCompareIsValue( $expressionLeft )){
      if( $this->expressionCompareIsValue( $expressionRight )){
        return T_VALUE_AND_VALUE;
      }
    }
    if( $this->expressionCompareIsValue( $expressionLeft )){
      if( $this->expressionCompareIsField( $expressionRight )){
        return T_VALUE_AND_FIELD;
      }
    }

    return 0;
  }

  public function adjustCompareSimple(
    array $tokens
  ): array {
    if( count( $tokens ) === 1 ){
      [ $expression ] = $tokens;
      if( $expression[ T_KEY_OBJECT ] === T_EXPRESSION_FIELD ){
        return [ sprintf( "%s.%s", $expression[ T_KEY_ENTITY ], $expression[ T_KEY_FIELD ])];
      }
    } else
    if( count( $tokens ) === 3 ){
      [ $expressionLeft, $expressionEqual, $expressionRight ] = $tokens;
      if( $expressionLeft[ T_KEY_OBJECT ] === T_EXPRESSION_FIELD ){
        if( $expressionEqual[ T_KEY_OBJECT ] === T_EXPRESSION_EQUAL ){
          if( $expressionRight[ T_KEY_OBJECT ] === T_EXPRESSION_VALUE ){
            return [
              sprintf( "%s.%s", $expressionLeft[ T_KEY_ENTITY ], $expressionLeft[ T_KEY_FIELD ]), 
              $expressionEqual[ T_KEY_VALUE ], $expressionRight[ T_KEY_VALUE ]
            ];
          } 
        }
      }
      if( $expressionLeft[ T_KEY_OBJECT ] === T_EXPRESSION_FIELD ){
        if( $expressionEqual[ T_KEY_OBJECT ] === T_EXPRESSION_EQUAL ){
          if( $expressionRight[ T_KEY_OBJECT ] === T_EXPRESSION_FIELD ){
            return [
              sprintf( "%s.%s", $expressionLeft[ T_KEY_ENTITY ], $expressionLeft[ T_KEY_FIELD ]), $expressionEqual[ T_KEY_VALUE ],
              sprintf( "%s.%s", $expressionRight[ T_KEY_ENTITY ], $expressionRight[ T_KEY_FIELD ])
            ];
          }
        }
      }
    }


    return $tokens;
  }

  public function expressionCompareIsObject(
    array $tokens
  ): bool {
    return isset( $tokens[ T_KEY_OBJECT ]) 
      ? $tokens[ T_KEY_OBJECT ] === T_EXPRESSION_COMPARE 
      : false;
  }

  public function preCompileImplodeTokens(
    array $tokens
  ): array {
    if( count( $tokens ) <= 2 ){
      return $tokens;
    }

    for($i = 0; $i < count($tokens); $i++){
      if( $this->expressionCompareIsObject( $tokens[ $i ]) === false ){
        continue;
      }

      $expressionICompareType = in_array( 
        $this->expressionCompareType( $tokens[ $i ]), [
          T_FIELD_AND_FIELD, T_VALUE_AND_VALUE, T_VALUE_AND_FIELD
        ]
      );

      if( $expressionICompareType ){ 
        continue;
      }
      
      [ $expressionILeft, $expressionIEqual, $expressionIRight
      ] = $tokens[ $i ][ T_KEY_TOKENS ];
      
      for($j = $i + 1; $j < count($tokens); $j++){
        if( $this->expressionCompareIsObject( $tokens[ $j ])){
          $expressionJCompareType = in_array( 
            $this->expressionCompareType( $tokens[ $j ]), [
              T_FIELD_AND_FIELD, T_VALUE_AND_VALUE, T_VALUE_AND_FIELD
            ]
          );

          if( $expressionJCompareType ){
            continue;
          }

          [ $expressionJLeft, $expressionJEqual, $expressionJRight 
          ] = $tokens[ $j ][ T_KEY_TOKENS ];

          if( $expressionILeft[ T_KEY_TYPE ] === $expressionJLeft[ T_KEY_TYPE ]){
            if( $expressionILeft[ T_KEY_TYPE ] === Datetime::class && $expressionJLeft[ T_KEY_TYPE ] === Datetime::class ){
              if( $expressionIEqual[ T_KEY_TOKENS ][ T_KEY_VALUE ] !== $expressionJEqual[ T_KEY_TOKENS ][ T_KEY_VALUE ]){
                $tokens[$i] = [
                  T_KEY_OBJECT => T_EXPRESSION_BETWEEN,
                  T_KEY_TOKENS => [ $expressionILeft, $expressionIRight, $expressionJRight ]
                ];
                
                $tokens[$j - 1][T_KEY_OBJECT] === T_EXPRESSION_LOGICAL 
                  ? array_splice($tokens, $j - 1, 2) : array_splice($tokens, $j, 1);
              }
            }
          }
        } 
      }
    }

    return $tokens;
  }

  public function preCompileRevaliderTokens(
    array $tokens = []
  ): array {
    foreach($tokens as $key => $token){
      $expressionObject = $token[ T_KEY_OBJECT ];
      
      $expressionObjectIsRevalider = in_array(
        $expressionObject, [ T_EXPRESSION_COMPARE, T_EXPRESSION_UNARY, T_EXPRESSION_NEGATIVE ]
      ) === false;
      
      if( $expressionObjectIsRevalider ){
        continue;
      }

      if( $expressionObject === T_EXPRESSION_UNARY ){
        [ $expressionField ] = $token[ T_KEY_TOKENS ];
        [ T_KEY_METHOD => $methods ] = $expressionField;

        $methodCompareList = array_values( array_filter( 
          $methods, fn( array $method ) => $method[T_KEY_ACTION] === T_METHODS_ACTION_COMPARE 
        ));

        if( count( $methodCompareList ) !== 0 ){
          [ $methodCompare ] = $methodCompareList;
          [ T_KEY_ENTITY => $table, T_KEY_FIELD => $field, T_KEY_TYPE => $type ] = $expressionField;

          $isMethodCompareLikes = in_array( 
            $methodCompare[ T_KEY_METHODS ], [
              T_METHODS_START_WITH, T_METHODS_NOT_START_WITH,
              T_METHODS_END_WITH, T_METHODS_NOT_END_WITH,
              T_METHODS_CONTAINS, T_METHODS_NOT_CONTAINS,
            ]
          );

          if( $isMethodCompareLikes ){
            $tokens[ $key ] = [
              T_KEY_OBJECT => match( $methodCompare[ T_KEY_METHODS ]){
                T_METHODS_NOT_START_WITH, T_METHODS_NOT_END_WITH, T_METHODS_NOT_CONTAINS  => T_EXPRESSION_NOT_LIKE,
                  default => T_EXPRESSION_LIKE
              },
              T_KEY_TOKENS => [
                [ T_KEY_OBJECT => T_EXPRESSION_FIELD, T_KEY_ENTITY => $table, T_KEY_FIELD => $field, T_KEY_TYPE => $type,
                  T_KEY_METHODS => array_values( array_filter( 
                    $methods, fn( array $method ) => $method[T_KEY_ACTION] !== T_METHODS_ACTION_COMPARE 
                  ))
                ], [ 
                  T_KEY_OBJECT => T_EXPRESSION_VALUE,
                  T_KEY_ISLIST => count( $methodCompare[ T_KEY_ARGS ]) === 1 
                    ? T_KEY_NO : T_KEY_YES,
                  T_KEY_VALUE  => count( $methodCompare[ T_KEY_ARGS ]) === 1 
                    ? $methodCompare[ T_KEY_ARGS ] : $methodCompare[ T_KEY_ARGS ]
                ]
              ]
            ];
          }
        }
      } else
      if( $expressionObject === T_EXPRESSION_COMPARE ){
        [ $expressionField, $expressionEqual, $expressionValue ] = $token[ T_KEY_TOKENS ];
        [ T_KEY_METHODS => $methods ] = $expressionField;

        if( in_array( $expressionEqual[ T_KEY_TOKENS ][ T_KEY_VALUE ], [ T_IS_EXPRESSION_EQUAL_IN, T_IS_EXPRESSION_EQUAL_NOT_IN ] )){
          $tokens[ $key ] = [
            T_KEY_OBJECT => $expressionEqual[ T_KEY_TOKENS ][ T_KEY_VALUE ] === T_IS_EXPRESSION_EQUAL_IN ? T_EXPRESSION_IN : T_EXPRESSION_NOT_IN,
            T_KEY_TOKENS => [
              array_merge( $expressionField, [
                T_KEY_METHODS => array_values( array_filter( 
                  $methods, fn( array $method ) => $method[T_KEY_ACTION] !== T_METHODS_ACTION_COMPARE 
                ))
              ]), $expressionValue
            ]
          ];
        } else
        if( in_array( $expressionEqual[ T_KEY_TOKENS ][ T_KEY_VALUE ], [ T_IS_EXPRESSION_EQUAL_LIKE, T_IS_EXPRESSION_EQUAL_NOT_LIKE ])){
          $tokens[ $key ] = [
            T_KEY_OBJECT => $expressionEqual[ T_KEY_TOKENS ][ T_KEY_VALUE ] === T_IS_EXPRESSION_EQUAL_LIKE ? T_EXPRESSION_LIKE : T_EXPRESSION_NOT_LIKE,
            T_KEY_TOKENS => [
              array_merge( $expressionField, [
                T_KEY_METHODS => array_values( array_filter( 
                  $methods, fn( array $method ) => $method[T_KEY_ACTION] !== T_METHODS_ACTION_COMPARE 
                ))
              ]), array_merge( $expressionValue, [ T_KEY_VALUE => [ $expressionValue[ T_KEY_VALUE ]]])
            ]
          ];
        }
      }
    }

    return $tokens;
  }

  public function getTokensMethods(
    Closure $closure,
    array $expressionLeft,
    array $tokens = [],
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if( $this->isObjectOperator($token) && $depth === 0){
        if( $curr ){
          $accu[] = $curr;
          $curr = [];
        } 
        
        continue;
      }

      $curr[] = $token;

      if( $this->startParentese( $token )) $depth++;
      if( $this->endParentese( $token )) $depth--;
    }

    if( $curr ){
      $accu[] = $curr;
    }

    return array_map(
      function(array $args) use( $closure, $expressionLeft ){
        [ $methodName, $methodoArgs ] = [ ...array_slice( $args, 0, 1 ), [ ...array_slice( $args, 
            $this->findNext( $args, T_START_PARENTESES ),
            $this->findPrev( $args, T_END_PARENTESES) - 1
          )]
        ];

        $methodoArgs = $this->chuckTokensByNumber( 
          $methodoArgs, T_COMMA
        );

        $methodoArgs = array_map(fn( array $tokens ) => $this->dropCurlOpenAndNotDot( $tokens ), $methodoArgs);
        $methodoArgs = array_map(fn( array $tokens ) => $this->updateTokensVariable( $tokens, $closure ), $methodoArgs);
        $methodoArgs = array_map(fn( array $tokens ) => $this->updateTokensEnums( $tokens, $closure ), $methodoArgs);
        $methodoArgs = array_map(fn( array $tokens ) => $this->parseValueType( 
          $closure, $expressionLeft, match( $methodName[ T_KEY_VALUE ]){
            T_METHODS_START_WITH, T_METHODS_NOT_START_WITH => sprintf( '%%%s', $this->adjustValues( $tokens )),
            T_METHODS_END_WITH, T_METHODS_NOT_END_WITH => sprintf( '%s%%', $this->adjustValues( $tokens )),
            T_METHODS_CONTAINS, T_METHODS_NOT_CONTAINS => sprintf( '%%%s%%', $this->adjustValues( $tokens ))
          }), $methodoArgs
        );

        $methodTypes = in_array( $methodName[ T_KEY_VALUE ], T_METHODS_ACTION_MODIFY_LIST );

        return [ 
          T_KEY_METHODS => $methodName[ T_KEY_VALUE ],
          T_KEY_ACTION => $methodTypes ? T_METHODS_ACTION_MODIFY : T_METHODS_ACTION_COMPARE,
          T_KEY_ARGS => $methodoArgs
        ];
      }, $accu 
    );
  }  

  public function expressionFieldProps(
    array $tokens,
    array $scopes,
    Closure $closure
  ): array {
    [ $variable, $_, $variableName 
    ] = $tokens;
    
    if( $variable[ T_KEY_VALUE ]){
      $entityStructure = ClosureUtil::getEntityStructure(
        $this->getInstance( $variable[ T_KEY_VALUE ], $scopes )
      );

      if( $entityStructure instanceof EntityStructure ){
        $methodArgs = $this->getTokensMethods( 
          $closure, [ T_KEY_TYPE => $entityStructure->types[ $variableName[ T_KEY_VALUE ]]], array_slice( $tokens, 4 )
        );

        return [
          $entityStructure->entity[ T_KEY_ALIAS ],
          $entityStructure->alias[ $variableName[ T_KEY_VALUE ]] ?? $variableName[ T_KEY_VALUE ],
          $entityStructure->types[ $variableName[ T_KEY_VALUE ]], $methodArgs
        ];
      }
    }
    
    return [];
  } 

  public function isExpressionValueList(
    array $tokens
  ): string {
    [ $tokenStartBracket, $tokensEndBracket ] = [ 
      ...array_slice( $tokens, 0, 1 ),
      ...array_slice( $tokens, -1, 1)
    ];

    return $tokenStartBracket[ T_KEY_NUMBER ] === T_START_BRACKET
        && $tokensEndBracket[ T_KEY_NUMBER ] === T_END_BRACKET 
         ? T_KEY_YES : T_KEY_NO;
  }  

  public function isVariable(
    int $number
  ): bool {
    return $number === T_VARIABLE;
  }

  public function dropCurlOpenAndNotDot(
    array $tokens
  ): array {
    for($i=0; $i < count($tokens); $i++){
      [ T_KEY_NUMBER => $number ] = $tokens[$i];
      if(in_array( $number, [ T_CURLY_OPEN, T_END_BRACE, T_DOT ])){
        array_splice( $tokens, $i, 1);
      }
    }

    return $tokens;
  }

  public function updateTokensVariable(
    array $tokens,
    Closure $closure
  ): array {
    for( $i=0; $i < count($tokens); $i++ ){
      [ T_KEY_NUMBER => $number, T_KEY_VALUE => $value ] = $tokens[ $i ];
      if( in_array( $number, [ T_VARIABLE ])){
        $statics = ClosureUtil::getStatics( $closure );
        if( count( $statics )){
          $value = $statics[ ltrim($value, "$") ];
          $tokens[ $i ] = [
            T_KEY_NUMBER => T_STRING,
            T_KEY_VALUE => $value,
            T_KEY_TYPE => token_name( T_STRING )
          ];
        }
      }
    }

    return $tokens;
  }

  public function isEnumValueWithProperty(
    array $tokens
  ): bool {
    if( count($tokens) < 5 ){
      return false;
    }

    if( count($tokens) === 5 ){
      [ $enum, $double, $case, $operator, $property ] = $tokens;
        return $enum[ T_KEY_NUMBER ] === T_STRING 
            && $double[ T_KEY_NUMBER ] === T_DOUBLE_COLON 
            && $case[ T_KEY_NUMBER ] === T_STRING 
            && $operator[ T_KEY_NUMBER ] === T_OBJECT_OPERATOR 
            && $property[ T_KEY_NUMBER ] === T_STRING;
    }

    return false;
  }
  
  public function isEnumValueNotProperty(
    array $tokens
  ): bool {
    if( count($tokens) < 3 ){
      return false;
    }

    if( count($tokens) === 3 ){
      [ $enum, $double, $case ] = $tokens;
        return $enum[ T_KEY_NUMBER ] === T_STRING 
            && $double[ T_KEY_NUMBER ] === T_DOUBLE_COLON 
            && $case[ T_KEY_NUMBER ] === T_STRING;
    }

    return false;
  }
  
  public function updateEnumValue(
    Closure $closure,
    array $tokens
  ): string {
    $isTokensEnumWithPropertys = $this->isEnumValueWithProperty( $tokens );
    $isTokensEnumNotPropertys = $this->isEnumValueNotProperty( $tokens );

    if( $isTokensEnumWithPropertys ){
      [ $enum,, $case,, $property ] = $tokens;
    } else if( $isTokensEnumNotPropertys ) {
      [ $enum,, $case ] = $tokens;
    }

    $use = ClosureUtil::getUse( $closure, $enum[ T_KEY_VALUE ]);
    if( $use ){
      $constantEnum = sprintf( "%s::%s", $use, $case[ T_KEY_VALUE ]);
      if( defined( $constantEnum )){
        $enumCase = constant( $constantEnum );
        if( isset( $property )){
          return $property[ T_KEY_VALUE ] === T_KEY_NAME
            ? $enumCase->name : $enumCase->value;
        } else return $enumCase->value;
      }
    }

    return join( "", array_map( fn( array $token ) => $token[ T_KEY_VALUE ], $tokens ));
  }

  public function updateTokensEnums(
    array $tokens,    
    Closure $closure,
  ): array {
    for( $i=0; $i < count($tokens); $i++ ){
      $tokensEnumWithPropertys = array_slice( $tokens, $i, 5 );
      $tokensEnumNotPropertys = array_slice( $tokens, $i, 3 );

      $isTokensEnumWithPropertys = $this->isEnumValueWithProperty( $tokensEnumWithPropertys );
      $isTokensEnumNotPropertys = $this->isEnumValueNotProperty( $tokensEnumNotPropertys );

      if( $isTokensEnumWithPropertys ){
        $tokens[ $i ] = [
          T_KEY_NUMBER => T_STRING,
          T_KEY_VALUE => $this->updateEnumValue(
            $closure, array_slice(
              $tokens, $i, 5
            )
          ),
          T_KEY_TYPE => token_name(
            T_STRING
          )
        ];
      } else 
      if( $isTokensEnumNotPropertys ){
        $tokens[ $i ] = [
          T_KEY_NUMBER => T_STRING,
          T_KEY_VALUE => $this->updateEnumValue(
            $closure, array_slice(
              $tokens, $i, 3
            )
          ),
          T_KEY_TYPE => token_name(
            T_STRING
          )
        ];
      }

      if( $isTokensEnumWithPropertys ){
        array_splice( $tokens, $i + 1, 4 );
      } else
      if( $isTokensEnumNotPropertys ){
        array_splice( $tokens, $i + 1, 2 );
      };
    }

    return $tokens;
  }  

  public function adjustValues(
    array $tokens
  ): string {
    return join( "", array_map( 
      fn( array $token ) => trim($token[ T_KEY_VALUE ], "'\""), $tokens 
    ));
  }
  
  public function getInstance(
    string $variable,
    array $scopes
  ): string {
    [ $scope ] = array_values( array_filter( $scopes,
      fn( array $scope ) => $scope[T_KEY_VARIABLE] === $variable
    ));

    return $scope[ T_KEY_INSTANCE ];
  }

  public function entitySubQuery(
    array $scopes
  ): string {
    [ $scope ] = array_slice( $scopes, -1, 1 );

    $entityStructure = ClosureUtil::getEntityStructure(
      $this->getInstance( $scope[T_KEY_VARIABLE], $scopes )
    );

    return $entityStructure->entity[T_KEY_ALIAS];
  }

  public function getScopes(
    array $tokens,
    Closure|int $closure,
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
          T_KEY_INSTANCE => ClosureUtil::getUse( $closure, $instance[ T_KEY_VALUE ]), 
          T_KEY_VARIABLE => $variable[ T_KEY_VALUE ]
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