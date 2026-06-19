<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use function array_slice, is_string, is_array, defined, count, in_array, sprintf;

defined( 'T_HASH' ) || define( 'T_HASH', 'hash' );
defined( 'T_CONTEXTS' ) || define( 'T_CONTEXTS', 'contexts' );

defined( 'T_START_PARENTESES' ) || define( 'T_START_PARENTESES', 40 );
defined( 'T_END_PARENTESES' ) || define( 'T_END_PARENTESES', 41 );
defined( 'T_START_BRACKET' ) || define( 'T_START_BRACKET', 91 );
defined( 'T_END_BRACKET' ) || define( 'T_END_BRACKET', 93 );
defined( 'T_START_BRACE' ) || define( 'T_START_BRACE', 123 );
defined( 'T_END_BRACE' ) || define( 'T_END_BRACE', 125 );
defined( 'T_DOT' ) || define( 'T_DOT', 46 );
defined( 'T_COMMA' ) || define( 'T_COMMA', 44 );
defined( 'T_SEMICOLON' ) || define( 'T_SEMICOLON', 59 );
defined( 'T_COLON' ) || define( 'T_COLON', 58 );
defined( 'T_QUESTION' ) || define( 'T_QUESTION', 63 );
defined( 'T_PLUS' ) || define( 'T_PLUS', 43 );
defined( 'T_MINUS' ) || define( 'T_MINUS', 45 );
defined( 'T_MULTIPLY' ) || define( 'T_MULTIPLY', 42 );
defined( 'T_DIVIDE' ) || define( 'T_DIVIDE', 47 );
defined( 'T_EQUAL' ) || define( 'T_EQUAL', 61 );
defined( 'T_GREATER_THAN' ) || define( 'T_GREATER_THAN', 62 );
defined( 'T_LESS_THAN' ) || define( 'T_LESS_THAN', 60 );
defined( 'T_NOT' ) || define( 'T_NOT', 33 );

defined( 'T_EXP_INITIAL' ) || define( 'T_EXP_INITIAL', 'ExpIntial' );
defined( 'T_EXP_DENYING' ) || define( 'T_EXP_DENYING', 'ExpDenying' );
defined( 'T_EXP_GROUP' ) || define( 'T_EXP_GROUP', 'ExpGroup' );
defined( 'T_EXP_LOGICAL' ) || define( 'T_EXP_LOGICAL', 'ExpLogical' );
defined( 'T_EXP_COMPARE' ) || define( 'T_EXP_COMPARE', 'ExpCompare' );
defined( 'T_EXP_BETWEEN' ) || define( 'T_EXP_BETWEEN', 'ExpBetween' );
defined( 'T_EXP_ISNULL' ) || define( 'T_EXP_ISNULL', 'ExpNull' );
defined( 'T_EXP_ISNOTNULL' ) || define( 'T_EXP_ISNOTNULL', 'ExpNotNull' );
defined( 'T_EXP_LIKE' ) || define( 'T_EXP_LIKE', 'ExpLike' );
defined( 'T_EXP_IN' ) || define( 'T_EXP_IN', 'ExpIn' );
defined( 'T_EXP_UNARY' ) || define( 'T_EXP_UNARY', 'ExpUnary' );
defined( 'T_EXP_SUBQUERY' ) || define( 'T_EXP_SUBQUERY', 'ExpSubQuery' );
defined( 'T_EXP_FIELD' ) || define( 'T_EXP_FIELD', 'ExpField' );
defined( 'T_EXP_EQUAL' ) || define( 'T_EXP_EQUAL', 'ExpEqual' );
defined( 'T_EXP_VALUE' ) || define( 'T_EXP_VALUE', 'ExpValue' );

defined( 'T_Entity' ) || define( 'T_Entity', 'entity' );
defined( 'T_Columns' ) || define( 'T_Columns', 'columns' );
defined( 'T_Types' ) || define( 'T_Types', 'types' );
defined( 'T_Alias' ) || define( 'T_Alias', 'alias' );
defined( 'T_Indexes' ) || define( 'T_Indexes', 'indexes' );
defined( 'T_Uniques' ) || define( 'T_Uniques', 'uniques' );
defined( 'T_Foreign_Keys' ) || define( 'T_Foreign_Keys', 'foreign_keys' );
defined( 'T_Primary_Keys' ) || define( 'T_Primary_Keys', 'primary_keys' );
defined( 'T_Not_Nulls' ) || define( 'T_Not_Nulls', 'not_nulls' );
defined( 'T_Auto_Increments' ) || define( 'T_Auto_Increments', 'auto_increments' );

defined( 'T_ACTION_TO_ADJUST_SIDE' ) || define( 'T_ACTION_TO_ADJUST_SIDE', 1 );
defined( 'T_ACTION_TO_ADJUST_EQUALS' ) || define( 'T_ACTION_TO_ADJUST_EQUALS', 2 );
defined( 'T_ACTION_TO_METHODS' ) || define( 'T_ACTION_TO_METHODS', 3 );
defined( 'T_ACTION_TO_BETWEEN' ) || define( 'T_ACTION_TO_BETWEEN', 4 );
defined( 'T_ACTION_TO_LIKE' ) || define( 'T_ACTION_TO_LIKE', 5 );
defined( 'T_ACTION_TO_IN' ) || define( 'T_ACTION_TO_IN', 6 );

defined( 'T_OBJECT' ) || define( 'T_OBJECT', 'object' );
defined( 'T_PARENT' ) || define( 'T_PARENT', 'parent' );
defined( 'T_CHILDS' ) || define( 'T_CHILDS', 'childs' );
defined( 'T_METHOD' ) || define( 'T_METHOD', 'method' );
defined( 'T_VALUES' ) || define( 'T_VALUES', 'values' );
defined( 'T_VALUES_TYPE' ) || define( 'T_VALUES_TYPE', 'valuesType' );
defined( 'T_SCHEME' ) || define( 'T_SCHEME', 'scheme' );
defined( 'T_COLUMN' ) || define( 'T_COLUMN', 'column' );
defined( 'T_COLUMN_TYPE' ) || define( 'T_COLUMN_TYPE', 'columnType' );
defined( 'T_COLUMN_METHODS' ) || define( 'T_COLUMN_METHODS', 'columnMethods' );
defined( 'T_COLUMN_METHOD_NAME' ) || define( 'T_COLUMN_METHOD_NAME', 'name' );
defined( 'T_COLUMN_METHOD_TYPE' ) || define( 'T_COLUMN_METHOD_TYPE', 'type' );
defined( 'T_COLUMN_METHOD_ARGS' ) || define( 'T_COLUMN_METHOD_ARGS', 'args' );

defined( 'T_TOKEN_KEY' ) || define( 'T_TOKEN_KEY', 'tokenKey' );
defined( 'T_TOKEN_NAME' ) || define( 'T_TOKEN_NAME', 'tokenName' );
defined( 'T_TOKEN_VALUE' ) || define( 'T_TOKEN_VALUE', 'tokenValue' );

defined( 'T_SUB_QUERY_LIST' ) || define( 'T_SUB_QUERY_LIST', [ 'any' ] );

class Utils
{
  public function inc(
    int $number
  ): int {
    return ++$number;
  }

  public function dec(
    int $number,
    int $extraDec = 0
  ): int {
    return ( --$number ) - $extraDec;
  }

  public function slice(
    array $items,
    int $offset,
    int|null $length = null
  ): array {
    return array_slice( $items, $offset, $length );
  }  

  public function mapper(
    array $items,
    Closure $closure
  ): array {
    $result = [];

    foreach( $items as $key => $item ){
      $result[$key] = $closure($item, $key);
    }

    return $result;
  }

  public function filter(
    array $items,
    Closure $closure
  ): array {
    $result = [];

    foreach( $items as $item ){
      if( $closure( $item )){
        $result[] = $item;
      }
    }

    return $result;
  }

  public function indexOf(
    array $tokens,
    int $type
  ): int {
    foreach( $tokens as $cursor => $token ){
      if( $token[T_TOKEN_KEY] === $type ){
        return $cursor;
      }
    }

    return -1;
  }

  public function groupByTypes(
    array $type,
    array $tokens,
     bool $showKey = false,
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if( in_array( $token[T_TOKEN_KEY], $type ) && $depth === 0){
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

      if($token[T_TOKEN_KEY] === T_START_PARENTESES) $depth++;
      if($token[T_TOKEN_KEY] === T_END_PARENTESES) $depth--;
    }

    if( $curr ){
      $accu[] = $curr;
    }

    return $accu;
  }

  public function groupByTypesLogical(
    array $contexts = []
  ): array {
    return $this->groupByTypes([ 
      T_LOGICAL_AND, T_LOGICAL_OR, T_BOOLEAN_AND, T_BOOLEAN_OR 
    ], $contexts, true );
  }  

  public function contextsNotEnds(
    array $contexts,
    int $parenteses = 0
  ): array {
    for($i=0; $i<count($contexts); $i++){
      if($contexts[$i][T_TOKEN_KEY] === T_START_PARENTESES){
        $parenteses++;
      }

      if($contexts[$i][T_TOKEN_KEY] === T_END_PARENTESES){
        $parenteses--;

        if($parenteses < 0){
          $contexts = array_slice(
            $contexts, 0, $i
          ); break;
        }          
      }

      if($parenteses < 1){
        if($contexts[$i][T_TOKEN_KEY] === T_SEMICOLON){
          $contexts = array_slice(
            $contexts, 0, $i
          ); break;
        }
      }
    };

    return $contexts;
  }  

  public function namberToken(
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

  public function createToken(
    string|array $tokenArgs
  ): array {
    [ $tokenKey, $tokenValue ] = is_string( $tokenArgs ) 
      ? [ ord( $tokenArgs ), $tokenArgs ] : $tokenArgs;

    if( in_array( $tokenKey, [ T_CONSTANT_ENCAPSED_STRING ])){
      $tokenValue = trim( $tokenValue, '"\'' );
    }  

    return [
      T_TOKEN_KEY => $tokenKey,
      T_TOKEN_VALUE => $tokenValue, 
      T_TOKEN_NAME => $this->namberToken(
        $tokenKey
      )
    ];
  }  
  
  public function tokenized(
    array $codeArr
  ): array {
    $tokens = array_slice(
      token_get_all( sprintf(
        "<?php %s", implode( "", $codeArr )
      )), 1 , null, true
    );

    $tokens = $this->filter( $tokens,
      fn( array|string $token ) => (
        is_string( $token ) || is_array( $token ) && $token[0] !== T_WHITESPACE
      )
    );

    $tokens = $this->mapper(
      $tokens, fn( array|string $token ) => (
        $this->createToken( $token )
      )
    );

    return $this->contextsNotEnds( $tokens );
  }

  public function isDenying(
    array $tokens = []
  ): bool {
    return $tokens[0][T_TOKEN_KEY] === T_NOT;
  }
  
  public function isGroup(
    array $tokens = []
  ): bool {
    return $tokens[0][T_TOKEN_KEY] === T_START_PARENTESES;
  }
  
  public function getSubQueryMethod(
    array $tokens = []    
  ): string|null {
    $subQueryMethods = array_slice(
      $tokens, $this->dec(
        $this->indexOf( $tokens, T_FN ), 1
      ), 1
    );

    if( empty( $subQueryMethods )){
      return null;
    }

    [ $subQueryMethod ] = $subQueryMethods;
    return $subQueryMethod[T_TOKEN_VALUE] ?? null;
  }  

  public function isSubQuery(
    array $contexts = []    
  ): bool {
    return in_array( $this->getSubQueryMethod( $contexts ), T_SUB_QUERY_LIST );
  }
  
  public function isLogical(
    array $contexts = []
  ): bool {
    return in_array( $contexts[0][T_TOKEN_KEY], [
      T_LOGICAL_AND, T_LOGICAL_OR, T_BOOLEAN_AND, T_BOOLEAN_OR 
    ]);
  }
  
  public function isCompare(
    array $contexts = []
  ): bool {
    return empty(
      $this->filter( $contexts, fn(array $token) => in_array( $token[T_TOKEN_KEY], [
        T_IS_NOT_IDENTICAL, T_IS_GREATER_OR_EQUAL, T_IS_SMALLER_OR_EQUAL,
        T_EQUAL, T_IS_EQUAL, T_IS_IDENTICAL, T_IS_NOT_EQUAL,
        T_GREATER_THAN, T_LESS_THAN
      ]))
    ) ? false : true;
  }

  public function isUnary(
    array $tokens = []
  ): bool {
    return $this->isCompare($tokens) === false;
  }  

  public function getExpType(
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

  public function fieldProps(
    array $contexts = []
  ): array {
    [ $scopeVariable, $_, $fieldVariable ] = $contexts;
    return [ $scopeVariable[T_TOKEN_VALUE], $fieldVariable[T_TOKEN_VALUE] ];
  }
  
  public function fieldPropByEntity(
    string $entity,
    string $column
  ): array {
    $cacheEntitys = Cache::entity(
      $entity
    );

    [ $columnScheme ] = $cacheEntitys[
      T_Entity
    ];

    [ $columnName ] = [ 
      $cacheEntitys[ T_Alias ][ $column ]
        ?? $column
    ];

    [ $columnType ] = array_slice(
      explode( '\\', $cacheEntitys[ T_Types ][ $column ] ), -1, 1
    );

    return [ 
      T_SCHEME => $columnScheme, 
      T_COLUMN => $columnName,
      T_COLUMN_TYPE => $columnType
    ];
  }
  
  public function scopeByField(
    array $scopes,
    string $scopeVariable 
  ): string|null {
    if( empty( $scopes )){
      return null;
    }

    $scopes = $this->filter(
      $scopes, fn( array $scope ) => (
        $scope[K_VARIABLE] === $scopeVariable
      ) 
    );
    
    if( empty( $scopes )){
      return null;
    }
    
    return $scopes[0][K_STATEMENTS];
  }
  
  public function fieldMethods(
    array $childs = [],
    array $events = []
  ): array {
    $events = $this->groupByTypes(
      [ T_OBJECT_OPERATOR ], $this->slice( $childs, 4 )
    );

    $events = $this->mapper(
      $events, fn( array $tokens ) => [
        T_COLUMN_METHOD_NAME => $tokens[0][T_TOKEN_VALUE], 
        T_COLUMN_METHOD_TYPE => in_array(
          $tokens[0][T_TOKEN_VALUE], [ 
            'contains',
            'startsWith',
            'endsWith',
            'in',
            'isNull',
            'isNotNull'
          ]) ? 'compare' : 'modify', 
        T_COLUMN_METHOD_ARGS => $this->mapper(
            $this->groupByTypes(
            [ T_COMMA ], array_slice(
              $tokens, $this->inc(
                $this->indexOf(
                  $tokens, T_START_PARENTESES
                )
              ), -1
            ), 
          ), fn( array $args ) => $args[0]
        )
      ]
    );

    return [ T_COLUMN_METHODS => $events ];
  }

  public function isField(
    array $contexts = []
  ): bool {
    if( count( $contexts ) < 3 ){
      return false;
    }

    return $contexts[0][T_TOKEN_KEY] === T_VARIABLE
        && $contexts[1][T_TOKEN_KEY] === T_OBJECT_OPERATOR
        && $contexts[2][T_TOKEN_KEY] === T_STRING;
  }  
  
  public function createField(
    array $scopes,
    array $childs = []
  ): array {
    [ $variable, $column 
    ] = $this->fieldProps( $childs );
   
    return [ 
      T_OBJECT => T_EXP_FIELD, 
      ...array_merge(
        $this->fieldPropByEntity( 
          $this->scopeByField( 
            $scopes, $variable 
          ), $column
        ), $this->fieldMethods( $childs )
      )
    ];
  }

  public function variableToStatic(
    array $contexts,
    array $statics
  ): array {
    for($i=0; $i < count($contexts); $i++){
      $value = $contexts[$i][T_TOKEN_VALUE];
      
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
          $contexts[$i] = $this->createToken([
            T_STRING, $staticValue
          ]);
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
  
  public function isWithProperty(
    array $values
  ): bool {
    if( count( $values ) < 5 ){
      return false;
    }

    if( count( $values ) === 5 ){
      return $values[0][T_TOKEN_KEY] === T_STRING
          && $values[1][T_TOKEN_KEY] === T_DOUBLE_COLON
          && $values[2][T_TOKEN_KEY] === T_STRING
          && $values[3][T_TOKEN_KEY] === T_OBJECT_OPERATOR
          && $values[4][T_TOKEN_KEY] === T_STRING;
    }

    return false;
  }  
  
  public function isNotProperty(
    array $values
  ): bool {
    if( count( $values ) < 3 ){
      return false;
    }

    if( count( $values ) === 3 ){
      return $values[0][T_TOKEN_KEY] === T_STRING
          && $values[1][T_TOKEN_KEY] === T_DOUBLE_COLON
          && $values[2][T_TOKEN_KEY] === T_STRING;
    }

    return false;
  }
  
  public function changeEnumValue(
    array $statements,
    string $enum,
    string $case,
    string|null $proerty = null
  ): array {
    $statementsEnum = $this->filter(
      $statements, fn( array $statement ) => $statement[K_VARIABLE] === $enum
    );

    if( $statementsEnum ){
      [ $statementsEnum ] = $statementsEnum;
      $constantEnum = "{$statementsEnum[K_STATEMENTS]}::{$case}";
      if( defined( $constantEnum )){
        $constantEnum = constant( $constantEnum );
        return $proerty !== null
          ? $this->createToken([ T_STRING, $proerty === "name" ? $constantEnum->name : $constantEnum->value ])
          : $this->createToken([ T_STRING, $constantEnum->value ]);
      }
    }

    return $proerty !== null
       ? [ $this->createToken([ T_STRING, $enum ]),
           $this->createToken([ T_DOUBLE_COLON, "::" ]),
           $this->createToken([ T_STRING, $case ]),
           $this->createToken([ T_OBJECT_OPERATOR, "->" ]),
           $this->createToken([ T_STRING, $proerty ])]
       : [ $this->createToken([ T_STRING, $enum ]),
           $this->createToken([ T_DOUBLE_COLON, "::" ]),
           $this->createToken([ T_STRING, $case ])];
  }
  
  public function enumToStatic(
    array $values = [],
    array $statements = []
  ): array {
    for($i=0; $i<count($values); $i++){
      $withProperty = $this->slice( $values, $i, 5 );
      $notProperty = $this->slice( $values, $i, 3 );

      $isWithProperty = $this->isWithProperty( $withProperty );
      $isNotProperty = $this->isNotProperty( $notProperty );

      if( $isWithProperty ){
        $values[$i] = $this->changeEnumValue(
          $statements, 
          $withProperty[0][T_TOKEN_VALUE],
          $withProperty[2][T_TOKEN_VALUE],
          $withProperty[4][T_TOKEN_VALUE]
        );
      } else if( $isNotProperty ){
        $values[$i] = $this->changeEnumValue(
          $statements, 
          $withProperty[0][T_TOKEN_VALUE],
          $withProperty[2][T_TOKEN_VALUE]
        );
      } 
      
      if( $isWithProperty ){
        array_splice( $values, $i + 1, 4 );
      } else if( $isNotProperty ) {
        array_splice( $values, $i + 1, 2 );
      }      
    };

    return $values;
  }

  private function createParams(
    string $value,
    string $type,
    ExpressionWhere $expressionWhere
  ): string {
    if( isset( $expressionWhere->params[ "signaryId-{$expressionWhere->signaryId}"]) === false){
      $expressionWhere->params[ "signaryId-{$expressionWhere->signaryId}"] = [];
    }

    $paramOrder = count(
      $expressionWhere->params[
        "signaryId-{$expressionWhere->signaryId}"
      ]
    );

    $expressionWhere->params[
      "signaryId-{$expressionWhere->signaryId}"
    ][ ":param_{$expressionWhere->signaryId}_{$paramOrder}" ] = $expressionWhere->expressionType->encode($value, $type);
    
    return '?';
  }  

  public function staticToParam(
    array $values,
    string $type,
    ExpressionWhere $expressionWhere
  ): string {
    $isStartBracket = $this->slice($values, 0, 1)[0][T_TOKEN_KEY] === T_START_BRACKET;
    $isEndBracket = $this->slice($values,-1, 1)[0][T_TOKEN_KEY] === T_END_BRACKET;

    if( $isStartBracket && $isEndBracket ){
      $valuesItems = $this->groupByTypes([ T_COMMA ], $this->slice($values, 1, -1));
      $valuesItems = $this->mapper( $valuesItems, fn(array $items) => (
        $this->createParams( $items[0][T_TOKEN_VALUE], $type, $expressionWhere )
      ));

      return sprintf( "(%s)", implode( ",", $valuesItems ));
    }

    return implode( "", $this->mapper($values, fn( array $token ) => (
      $this->createParams( $token[T_TOKEN_VALUE], $type, $expressionWhere )
    )));
  }
}