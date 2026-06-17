<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use function ord, count, in_array, is_string, array_slice, array_merge, sprintf;
use ReflectionFunction;

define( 'T_HASH', 'hash' ); 
define( 'T_CONTEXTS', 'contexts' );

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

define( 'T_EXP_INITIAL', 'ExpIntial' );
define( 'T_EXP_DENYING', 'ExpDenying' );
define( 'T_EXP_GROUP', 'ExpGroup' );
define( 'T_EXP_LOGICAL', 'ExpLogical' );
define( 'T_EXP_COMPARE', 'ExpCompare' );
define( 'T_EXP_BETWEEN', 'ExpBetween' );
define( 'T_EXP_ISNULL', 'ExpNull' );
define( 'T_EXP_ISNOTNULL', 'ExpNotNull' );
define( 'T_EXP_LIKE', 'ExpLike' );
define( 'T_EXP_IN', 'ExpIn' );
define( 'T_EXP_UNARY', 'ExpUnary' );
define( 'T_EXP_SUBQUERY', 'ExpSubQuery' );
define( 'T_EXP_FIELD', 'ExpField' );
define( 'T_EXP_EQUAL', 'ExpEqual' );
define( 'T_EXP_VALUE', 'ExpValue' );

define( 'T_Entity', 'entity' );
define( 'T_Columns', 'columns' );
define( 'T_Types', 'types' );
define( 'T_Alias', 'alias' );
define( 'T_Indexes', 'indexes' );
define( 'T_Uniques', 'uniques' );
define( 'T_Foreign_Keys', 'foreign_keys' );
define( 'T_Primary_Keys', 'primary_keys' );
define( 'T_Not_Nulls', 'not_nulls' );
define( 'T_Auto_Increments', 'auto_increments' );

define( 'T_ACTION_TO_ADJUST_SIDE', 1 );
define( 'T_ACTION_TO_ADJUST_EQUALS', 2 );
define( 'T_ACTION_TO_METHODS', 3 );
define( 'T_ACTION_TO_BETWEEN', 4 );
define( 'T_ACTION_TO_LIKE', 5 );
define( 'T_ACTION_TO_IN', 6 );

define( "T_OBJECT", "object" );
define( "T_PARENT", "parent" );
define( "T_CHILDS", "childs" );
define( "T_VALUES", "values" );
define( "T_SCHEME", "scheme" );
define( "T_COLUMN", "column" );
define( "T_COLUMN_TYPE", "columnType" );
define( "T_COLUMN_METHODS", "columnMethods" );
define( "T_COLUMN_METHOD_NAME", "name" );
define( "T_COLUMN_METHOD_TYPE", "type" );
define( "T_COLUMN_METHOD_ARGS", "args" );

define( "T_TOKEN_KEY", "tokenKey" );
define( "T_TOKEN_NAME", "tokenName" );
define( "T_TOKEN_VALUE", "tokenValue" );

class ExpressionAbstract
{
  public ReflectionFunction $reflectionFunction;
  public bool $cacheHashEquals = false;  
  public string $cacheClass;
  public string $cacheMethod;
  public string $contextsBaseHash;
  public string $hash;
  public array $contexts = [];
  public array $contextsBase = [];  
  public array $scopes = [];
  public array $tokens = [];
  public array $uses = [];

  public function __construct(
    public Closure $closure
  ){
    calcTimer( "[PRE-CACHE] Construtor da class ExpressionAbstract" );
  }

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
    return array_map( $closure, $items, array_keys( $items ));
  }

  public function where(
    array $items,
    Closure $closure
  ): array {
    return array_values( array_filter( $items, $closure ));
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

  public function analysisLexicalTokens(
  ): void {
    $this->reflectionFunction = new ReflectionFunction( $this->closure );
    calcTimer( "[PRE-CACHE] Create ReflectionFunction ExpressionWhere::Tokens" );
    if( $this->reflectionFunction instanceof ReflectionFunction ){
      $this->tokens = file( $this->reflectionFunction->getFileName());
      calcTimer( "[PRE-CACHE] Ler aquivo ExpressionWhere::Tokens" );

      if( count( $this->tokens ) !== 0 ){
        $this->tokens = $this->mapper(
          $this->tokens, function(string $token){
            $strPos = strpos( $token, '//' );
            return $strPos ? substr($token, 0, $strPos) : $token;
          }
        );
      }
    }
  }
  
  public function analysisLexicalOrigins(
  ): void {
    var_dump( $this->reflectionFunction->getShortName() );
    for( $i=count( $this->tokens ) - 1; $i>=0; $i-- ){
      if( strpos( $this->tokens[$i], 'function' ) !== false ){
        if( isset( $this->cacheMethod ) === false ){
          $this->cacheMethod = preg_replace([
            "#^.*function\s*#", "#\s*\(.*$#"
          ], "", trim( $this->tokens[ $i ]));
          calcTimer( "[PRE-CACHE] Search Function Name ExpressionWhere::Origins" );
        }
      }
      
      if( strpos( $this->tokens[$i], 'class' ) !== false ){
        if(isset( $this->cacheClass ) === false ){
          $this->cacheClass = preg_replace([
            "#^.*class\s*#", "#\s*\{.*$#"
          ], "", trim( $this->tokens[ $i ]));
          calcTimer( "[PRE-CACHE] Search Classe Name ExpressionWhere::Origins" );
        }
      }      
    }
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

  public function cacheFile(
  ): string {
    return implode( DIRECTORY_SEPARATOR, [
      BASEDIR_APP, "Cache", sprintf( "orm-where-%s.php", 
        md5( $this->cacheClass ), md5( $this->cacheMethod ) 
        //md5( $this->reflectionFunction->getShortName() )
      )
    ]);
  }

  public function analysisLexicalContexts(
  ): void {
    $this->contexts = $this->slice(
      token_get_all(
        sprintf( '<?php %s', implode(
          '', $this->slice(
            $this->tokens, 
            $this->reflectionFunction->getStartLine() - 1,
            $this->reflectionFunction->getEndLine() - 
            $this->reflectionFunction->getStartLine() + 1
          )
        ))
      ), 1
    ); 
    
    $this->contexts = $this->mapper(
      $this->contexts, fn(array|string $token) => (
        $this->createToken($token)
      ) 
    );

    $this->contexts = $this->contextsNotEnds(
      $this->where( $this->contexts, fn(array $token) => (
        $token[T_TOKEN_KEY] !== T_WHITESPACE
      ))
    );
  }

  public function analysisLexicalUses(
  ): void {
    $this->uses = $this->where(
      $this->tokens, fn(string $row) => str_starts_with( 
        trim( $row), 'use'
      )
    );

    $this->uses = $this->mapper(
      $this->uses, function(string $row){
        $use = str_replace(
          [ 'use',';' ], '', $row
        );

        if( strpos($use, 'as') !== false ){
          [ $use, $key ] = explode( 'as', $use );
          return [ trim($use), trim($key)];
        } else {
          $useImplits = explode('\\', $use);
          return [
            trim( implode( '\\', array_slice( $useImplits, -1 ))),
            trim( implode( '\\', array_slice( $useImplits, 0 )))
          ];
        }        
      }
    );
  } 

  public function analysisLexicalScopesExtracts(
    array $contexts = []
  ): array {
    $this->scopes = $this->groupByTypes(
      [ T_COMMA ], $this->slice(
        $this->slice( $contexts, 
          $this->inc( $this->indexOf( $contexts, T_FN )),
          $this->dec( $this->indexOf( $contexts, T_DOUBLE_ARROW ))
        ), 1, -1 
      )
    );

    return $this->mapper(
      $this->scopes, fn( array $scope ) => [
        $scope[1][T_TOKEN_VALUE], $this->where(
          $this->uses, fn( array $use ) => $use[0] === $scope[0][T_TOKEN_VALUE]
        )[0][1]
      ]
    );      
  }  

  public function analysisLexicalScopes(
  ): void {
    $this->scopes = $this->analysisLexicalScopesExtracts( $this->contexts );
    $this->contexts = $this->slice(
      $this->contexts, $this->inc( $this->indexOf(
        $this->contexts, T_DOUBLE_ARROW
      ))
    );
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
  ): array|null {
    $subQueryMethod = array_slice(
      $tokens, $this->dec(
        $this->indexOf( $tokens, T_FN ), 1
      ), 1
    );

    return $subQueryMethod ?? null;
  }  

  public function isSubQuery(
    array $contexts = []    
  ): bool {
    [ $subQueryMethod ] = $this->getSubQueryMethod($contexts);
    return in_array( $subQueryMethod[T_TOKEN_VALUE], [ 'any' ]);
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
      $this->where( $contexts, fn(array $token) => in_array( $token[T_TOKEN_KEY], [
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
  
  public function analysisLexicalHierarchySemanticsDenying(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    if( $this->getExpType( $this->slice( $childs, 1 )) === T_EXP_UNARY ){
      if( count( $this->slice( $childs, 1 )) === 3 ){
        return $this->analysisLexicalHierarchySemanticsCompare(
          $parent, $scopes, array_merge( 
            $this->slice( $childs, 1 ), [
              $this->createToken([ T_IS_IDENTICAL, "===" ]),
              $this->createToken([ T_STRING, "false" ])
            ]
          )
        );
      }
    }

    return [ 
      T_OBJECT => T_EXP_DENYING, 
      T_PARENT => $parent,
      T_CHILDS => $this->analysisLexicalHierarchySemantics(
        T_EXP_DENYING, $scopes, $this->slice( $childs, 1 )
      )
    ];
  }

  public function analysisLexicalHierarchySemanticsGroup(
    string $parent,
    array $scopes,
    array $childs = []
  ): array {
    return [ 
      T_OBJECT => T_EXP_GROUP,
      T_PARENT => $parent, 
      T_CHILDS => $this->analysisLexicalHierarchySemantics( 
        T_EXP_GROUP, $scopes, $this->slice( $childs, 1, -1 )
      )
    ];
  }
  
  public function analysisLexicalHierarchySemanticsSubQuery(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    return [
      T_OBJECT => T_EXP_SUBQUERY,
      T_PARENT => $parent,
      T_CHILDS => $this->analysisLexicalHierarchySemantics(
        T_EXP_SUBQUERY, array_merge( 
          $scopes, $this->analysisLexicalScopesExtracts(
            $this->slice( $childs, $this->indexOf( $childs, T_FN ))
          )
        ), $this->contextsNotEnds( 
            $this->slice( $childs, $this->inc(
              $this->indexOf( $childs, T_DOUBLE_ARROW )
            )
          )
        )
      )
    ];
  }
  
  public function analysisLexicalHierarchySemanticsLogical(
     array $contexts = []
  ): array {
    return [ 
      T_OBJECT => T_EXP_LOGICAL,
      T_VALUES => match( $contexts[0][T_TOKEN_KEY] ){
        T_BOOLEAN_AND, T_LOGICAL_AND => 'And',
        T_BOOLEAN_OR, T_LOGICAL_OR => 'Or',
          default => ''
      }
    ];
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

    $scopes = $this->where(
      $scopes, fn( array $scope ) => $scope[0] === $scopeVariable 
    );

    if( empty( $scopes )){
      return null;
    }

    return $scopes[0][1];
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
    ] = $this->fieldProps($childs);
   
    return [ 
      T_OBJECT => T_EXP_FIELD, 
      ...array_merge(
        $this->fieldPropByEntity( 
          $this->scopeByField( 
            $scopes, $variable 
          ), $column
        ), $this->fieldMethods($childs)
      )
    ];
  }
  
  public function createEqual(
    array $childs = []
  ): array {
    return [ T_OBJECT => T_EXP_EQUAL, T_VALUES => $childs[0] ];
  }
  
  public function createValue(
    array $childs = []
  ): array {
    return [ T_OBJECT => T_EXP_VALUE, T_VALUES => $childs ];
  }  

  public function reverseEqual(
    array $contents = []
  ): array {
    [ $type, $token ] = $contents;
    return [ $type, match( $contents[ 0 ]){
      T_IS_SMALLER_OR_EQUAL => $this->createToken( ">=" ),
      T_IS_GREATER_OR_EQUAL => $this->createToken( "<=" ),
      T_GREATER_THAN => $this->createToken( "<" ),
      T_LESS_THAN => $this->createToken( ">" ),
        default => $token
    }];
  } 
  
  public function parseEqual(
    string $equal
  ): string {
    if( in_array( $equal, [ "===", "==" ])){
      return "=";
    } else 
    if( in_array( $equal, [ "!==", "!=" ])){
      return "<>";
    } else return $equal;
  }
  
  public function analysisLexicalHierarchySemanticsCompare(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    [ $childA, $equals, $childB 
    ] = $this->groupByTypes([ 
      T_EQUAL,
      T_IS_EQUAL, T_IS_IDENTICAL,
      T_IS_NOT_EQUAL, T_IS_NOT_IDENTICAL,
      T_IS_GREATER_OR_EQUAL, T_IS_SMALLER_OR_EQUAL,
      T_GREATER_THAN, T_LESS_THAN 
    ], $childs, true );

    return [
      T_OBJECT => T_EXP_COMPARE, 
      T_PARENT => $parent, 
      T_CHILDS => [
        $this->isField( $childA ) 
          ? $this->createField( $scopes, $childA ) 
          : $this->createValue( $childA ), $this->createEqual( $equals ),
        $this->isField( $childB )
          ? $this->createField( $scopes, $childB ) 
          : $this->createValue( $childB )
      ]
    ];
  }
  
  public function analysisLexicalHierarchySemanticsUnary(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    return [ 
      T_OBJECT => T_EXP_UNARY,
      T_PARENT => $parent,
      T_CHILDS => [
        $this->createField( $scopes, $childs )
      ]
    ];
  }  

  public function analysisLexicalHierarchySemantics(
    string $parent,
    array $scopes,
    array $contexts = []
  ): array {
    return $this->mapper( 
      $this->groupByTypesLogical( $contexts ), fn( array $childs ) => (
        match( $this->getExpType( $childs )){
          T_EXP_DENYING => $this->analysisLexicalHierarchySemanticsDenying( $parent, $scopes, $childs ),
          T_EXP_GROUP => $this->analysisLexicalHierarchySemanticsGroup( $parent, $scopes, $childs ),
          T_EXP_SUBQUERY => $this->analysisLexicalHierarchySemanticsSubQuery( $parent, $scopes, $childs ),
          T_EXP_LOGICAL => $this->analysisLexicalHierarchySemanticsLogical( $childs ),
          T_EXP_COMPARE => $this->analysisLexicalHierarchySemanticsCompare( $parent, $scopes, $childs ),
          T_EXP_UNARY => $this->analysisLexicalHierarchySemanticsUnary( $parent, $scopes, $childs ),
            default => $childs
        }
      )
    );
  }  

  public function analysisLexicalHierarchy(
  ): void {
    $this->contexts = $this->analysisLexicalHierarchySemantics( 
      T_EXP_INITIAL, $this->scopes, $this->contexts
    );
  } 

  public function analysisLexicalHierarchySimpleSemanticsToAjustSide(
    array &$contexts,
    int $x
  ): void {
    if( $contexts[$x][T_CHILDS][0][T_OBJECT] === T_EXP_VALUE ){
      $contexts[$x][T_CHILDS][1][T_VALUES] = match( $contexts[$x][T_CHILDS][1][T_VALUES][T_TOKEN_KEY] ){
        T_IS_SMALLER_OR_EQUAL => $this->createToken( ">=" ),
        T_IS_GREATER_OR_EQUAL => $this->createToken( "<=" ),
        T_GREATER_THAN => $this->createToken( "<" ),
        T_LESS_THAN => $this->createToken( ">" ),
          default => $contexts[$x][T_CHILDS][1][T_VALUES]
      };

      $contexts[$x][T_CHILDS] = array_reverse(
        $contexts[$x][T_CHILDS]
      );
    }
  }

  public function analysisLexicalHierarchySimpleSemanticsToAjustEquals(
    array &$contexts,
    int $x
  ): void {
    for($y = 0; $y < count( $contexts[$x][T_CHILDS] ); $y++){
      if($contexts[$x][T_CHILDS][$y][T_OBJECT] === T_EXP_EQUAL){
        $contexts[$x][T_CHILDS][$y][T_VALUES] = match( $contexts[$x][T_CHILDS][$y][T_VALUES][T_TOKEN_KEY] ){
          T_IS_NOT_IDENTICAL, T_IS_NOT_EQUAL => $this->createToken( "<>" ), 
          T_IS_IDENTICAL, T_IS_EQUAL => $this->createToken( "=" ), 
            default => $contexts[$x][T_CHILDS][$y][T_VALUES]
        };
      }
    }
  } 
  
  public function analysisLexicalHierarchySimpleSemanticsToMethods(
    array &$contexts, int $x,
    array $contextsLinks = [],
    array $contextsArgs = []
  ): void {
    if($contexts[$x][T_CHILDS][0][T_OBJECT] === T_EXP_FIELD){
      if( empty( $contexts[$x][T_CHILDS][0][T_COLUMN_METHODS]) === false ){
        $methodCompare = $this->where( 
          $contexts[$x][T_CHILDS][0][T_COLUMN_METHODS], 
            fn(array $method) => $method[T_COLUMN_METHOD_TYPE] === "compare" 
        );

        $contexts[$x][T_CHILDS][0][T_COLUMN_METHODS] = $this->where( 
          $contexts[$x][T_CHILDS][0][T_COLUMN_METHODS], 
            fn(array $method) => $method[T_COLUMN_METHOD_TYPE] === "modify" 
        ); 
        
        if( empty( $methodCompare ) !== true ){
          if( in_array($methodCompare[0][T_COLUMN_METHOD_NAME], [ 'contains', 'startsWith', 'endsWith' ])){
            for( $y = 0; $y < count($methodCompare[0][T_COLUMN_METHOD_ARGS]); $y++ ){
              if( $y >= 1 ){
                $contextsLinks[] = [
                  T_OBJECT => T_EXP_LOGICAL,
                  T_VALUES => "Or"
                ];
              }

              if( $methodCompare[0][T_COLUMN_METHOD_NAME] === "contains" ){
                $contextsLinks[] = [ 
                  T_OBJECT => T_EXP_LIKE, 
                  T_PARENT => $contexts[$x][T_PARENT], 
                  T_CHILDS => [
                    $contexts[$x][T_CHILDS][0], [
                      T_OBJECT => T_EXP_VALUE, 
                      T_VALUES => array_merge(
                        [ $this->createToken([ T_STRING, '%' ]) ], [
                          $methodCompare[0][T_COLUMN_METHOD_ARGS][$y]
                        ], [ $this->createToken([ T_STRING, '%' ]) ]
                      )
                    ]
                  ]
                ];
              } else 
              if( $methodCompare[0][T_COLUMN_METHOD_NAME] === "startsWith" ){
                $contextsLinks[] = [
                  T_OBJECT => T_EXP_LIKE, 
                  T_PARENT => $contexts[$x][T_PARENT], 
                  T_CHILDS => [
                    $contexts[$x][T_CHILDS][0], [
                      T_OBJECT => T_EXP_VALUE, 
                      T_VALUES => array_merge(
                        [ $methodCompare[0][T_COLUMN_METHOD_ARGS][$y]], [
                          $this->createToken([ T_STRING, '%' ])
                        ]
                      )
                    ]
                  ]
                ];
              } else 
              if( $methodCompare[0][T_COLUMN_METHOD_NAME] === "endsWith" ){
                $contextsLinks[] = [
                  T_OBJECT => T_EXP_LIKE,
                  T_PARENT => $contexts[$x][T_PARENT], 
                  T_CHILDS => [
                    $contexts[$x][T_CHILDS][0], [
                      T_EXP_VALUE, array_merge(
                        [ $this->createToken([ T_STRING, '%' ]) ], [
                          $methodCompare[0][T_COLUMN_METHOD_ARGS][$y]
                        ]
                      )
                    ]
                  ]
                ];
              }
            }
        
            if( count( $contextsLinks ) === 1 ){
              $contexts[$x] = $contextsLinks[0];
            } else {
              for( $y = 0; $y < count($methodCompare[0][T_COLUMN_METHOD_ARGS]); $y++ ){
                if( $contextsLinks[$y][T_OBJECT] !== T_EXP_LOGICAL ){
                  $contextsLinks[$y][T_PARENT] = $contexts[$x][T_PARENT];
                }
              }

              $contexts[$x] = [
                T_OBJECT => T_EXP_GROUP,
                T_PARENT => $contexts[$x][T_PARENT],
                T_CHILDS => $contextsLinks
              ];
            } 
          } else
          if( in_array($methodCompare[0][T_COLUMN_METHOD_NAME], [ 'in' ])){
            foreach($methodCompare[0][T_COLUMN_METHOD_ARGS] as $cursor => $arg){
              if( $cursor > 0 ){
                $contextsArgs[] = $this->createToken([ T_COMMA, "," ]);
              }

              $contextsArgs[] = $arg;
            }

            $contexts[$x] = [
              T_OBJECT => T_EXP_IN, 
              T_PARENT => $contexts[$x][T_PARENT],
              T_CHILDS => [
                $contexts[$x][T_CHILDS][0], [
                  T_OBJECT => T_EXP_VALUE, 
                  T_VALUES => array_merge(
                    [ $this->createToken([ T_START_BRACKET, "[" ]) ], $contextsArgs,
                    [ $this->createToken([ T_END_BRACKET, "]" ]) ]
                  )
                ]
              ]
            ];
          } else
          if( in_array($methodCompare[0][T_COLUMN_METHOD_NAME], [ 'isNull', 'isNotNull' ])){
            $contexts[$x] = [
              T_OBJECT => $methodCompare[0][T_COLUMN_METHOD_NAME] === 'isNull' 
                ? T_EXP_ISNULL 
                : T_EXP_ISNOTNULL,
              T_PARENT => $contexts[$x][T_PARENT],
              T_CHILDS => [
                $contexts[$x][T_CHILDS]
              ]
            ];
          }
        }
      } 
    }
  }

  public function analysisLexicalHierarchySimpleSemanticsToBetween(
    array &$contexts,
    int $x, $y
  ): void {
    if( $contexts[$x][T_CHILDS][0][T_OBJECT] === T_EXP_FIELD ){
      if( $contexts[$y][T_CHILDS][0][T_OBJECT] === T_EXP_FIELD ){
        if( $contexts[$x][T_CHILDS][0][T_SCHEME] === $contexts[$y][T_CHILDS][0][T_SCHEME]){
          if( $contexts[$x][T_CHILDS][0][T_COLUMN] === $contexts[$y][T_CHILDS][0][T_COLUMN] ){
            if( $contexts[$x][T_CHILDS][0][T_COLUMN_TYPE] === "Datetime" && $contexts[$y][T_CHILDS][0][T_COLUMN_TYPE] === "Datetime" ){
              if( $contexts[$y - 1][T_VALUES] === "And" ){
                $contexts[$x][T_CHILDS][0][T_COLUMN_METHODS][] = [
                  T_COLUMN_METHOD_NAME => "date",
                  T_COLUMN_METHOD_TYPE => "modify", 
                  T_COLUMN_METHOD_ARGS => []
                ];

                $contexts[$x] = [
                  T_OBJECT => T_EXP_BETWEEN,
                  T_PARENT => $contexts[$x][T_PARENT], 
                  T_CHILDS => [
                    $contexts[$x][T_CHILDS][0], 
                    $contexts[$x][T_CHILDS][2],
                    $contexts[$y][T_CHILDS][2]
                  ]
                ];

                $y !== 0
                  ? array_splice( $contexts, $y - 1, 2 ) 
                  : array_splice( $contexts, $y, 1 );
              }
            }
          }
        }
      }
    };
  }

  public function analysisLexicalHierarchySimpleSemanticsToLike(
    array &$contexts,
    int $x
  ): void {
    if( $contexts[$x][T_CHILDS][0][T_COLUMN_TYPE] === "Text" ){
      if( $contexts[$x][T_CHILDS][2][T_OBJECT] === T_EXP_VALUE ){
        $valueToLike = implode( "", $this->mapper(
          $contexts[$x][T_CHILDS][2][T_VALUES], fn(array $value) => $value[T_TOKEN_VALUE]
        ));
        
        if( preg_match( "#(?<!\\\\)%#", $valueToLike )){
          $contexts[$x] = [ 
            T_OBJECT => T_EXP_LIKE, 
            T_PARENT => $contexts[$x][T_PARENT], 
            T_VALUES => [
              $contexts[$x][T_CHILDS][0], [
                T_EXP_VALUE, $contexts[$x][T_CHILDS][2][T_VALUES] 
              ]
            ]
          ];
        }
      }
    }
  } 

  public function analysisLexicalHierarchySimpleSemantics(
    int $convertType,
    array $contexts = []
  ): array {
    if( $convertType === T_ACTION_TO_ADJUST_SIDE ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][T_OBJECT], [ T_EXP_COMPARE ])){
          $this->analysisLexicalHierarchySimpleSemanticsToAjustSide( $contexts, $x );
        }
      }
    } else
    if( $convertType === T_ACTION_TO_ADJUST_EQUALS ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][T_OBJECT], [ T_EXP_COMPARE ])){
          $this->analysisLexicalHierarchySimpleSemanticsToAjustEquals( $contexts, $x );
        }
      }
    }
    if( $convertType === T_ACTION_TO_METHODS ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][T_OBJECT], [ T_EXP_UNARY, T_EXP_COMPARE ])){
          $this->analysisLexicalHierarchySimpleSemanticsToMethods( $contexts, $x );
        }
      }
    } else    
    if( $convertType === T_ACTION_TO_BETWEEN ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][T_OBJECT], [ T_EXP_COMPARE ])){
          for( $y = $x + 1; $y < count($contexts); $y++ ){
            if( in_array( $contexts[$y][T_OBJECT], [ T_EXP_COMPARE ])){
              $this->analysisLexicalHierarchySimpleSemanticsToBetween( $contexts, $x, $y );
            };      
          }
        }
      }
    } else
    if( $convertType === T_ACTION_TO_LIKE ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][T_OBJECT], [ T_EXP_UNARY, T_EXP_COMPARE ])){
          $this->analysisLexicalHierarchySimpleSemanticsToLike( $contexts, $x );
        }
      }
    }

    return $this->analysisLexicalHierarchySimples( $contexts );
  }  
  
  public function analysisLexicalHierarchySimples(
    array $contexts = []
  ): array {
    return $this->mapper( 
      $contexts, function(array $context){
        if( in_array($context[T_OBJECT], [T_EXP_GROUP, T_EXP_DENYING, T_EXP_SUBQUERY])){
          $context[T_CHILDS] = $this->analysisLexicalHierarchySimpleSemantics(T_ACTION_TO_ADJUST_SIDE, $context[T_CHILDS]);
          $context[T_CHILDS] = $this->analysisLexicalHierarchySimpleSemantics(T_ACTION_TO_ADJUST_EQUALS, $context[T_CHILDS]);
          $context[T_CHILDS] = $this->analysisLexicalHierarchySimpleSemantics(T_ACTION_TO_METHODS, $context[T_CHILDS]);
          $context[T_CHILDS] = $this->analysisLexicalHierarchySimpleSemantics( T_ACTION_TO_BETWEEN, $context[T_CHILDS]);
          $context[T_CHILDS] = $this->analysisLexicalHierarchySimpleSemantics( T_ACTION_TO_LIKE, $context[T_CHILDS]);
        }

        return $context;
      }
    );
  }  
  
  public function analysisLexicalSimples(
  ): void {
    $this->contexts = $this->analysisLexicalHierarchySimples( $this->contexts );
  }

  public function analysisLexicalInit(
  ): void {}  

  public function analysisLexicalClear(
  ): void {
    unset( $this->tokens );
    unset( $this->closure );
    unset( $this->cacheHashEquals );
    unset( $this->reflectionFunction );
  }  

  public function analysisLexicalBuild(
  ): void {
    $this->analysisLexicalContexts();
    calcTimer( "Criar Listagem de ExpressionWhere::Contexts" );
    $this->analysisLexicalUses();
    calcTimer( "Criar Listagem de ExpressionWhere::Uses" );
    $this->analysisLexicalScopes();
    calcTimer( "Criar Listagem de ExpressionWhere::Scopes" );
    $this->analysisLexicalHierarchy();
    calcTimer( "Criar Listagem de ExpressionWhere::Hierarchy" );
    $this->analysisLexicalSimples();
    calcTimer( "Criar Listagem de ExpressionWhere::Simples" );  
    $this->analysisLexicalInit();  
    calcTimer( "Criar Listagem de ExpressionWhere::Init" );  

    file_put_contents( 
      $this->cacheFile(), sprintf(
        "<?php%s%sreturn %s;", PHP_EOL, PHP_EOL, var_export([
          T_HASH => $this->contextsBaseHash, 
          T_CONTEXTS => $this->contexts
        ], true)
      ), LOCK_EX
    );
    
    $this->analysisLexicalClear();
    calcTimer( "Criar Listagem de ExpressionWhere::Clear" );
  }
  
  public function analysisLexicalCache(
  ): void {
    $this->contextsBase = $this->slice( $this->tokens, 
      $this->reflectionFunction->getStartLine() - 1,
      $this->reflectionFunction->getEndLine() - 
      $this->reflectionFunction->getStartLine() + 1
    );

    $this->contextsBaseHash = md5(
      json_encode( $this->contextsBase )
    );

    if( file_exists( $this->cacheFile())){
      [ T_HASH => $this->hash, T_CONTEXTS => $this->contexts 
      ] = require $this->cacheFile();
      calcTimer( "[PRE-CACHE] Require File ExpressionWhere::Cache" );
      if( $this->contextsBaseHash === $this->hash ){
        $this->analysisLexicalInit();
      } else $this->analysisLexicalBuild();
    } else $this->analysisLexicalBuild();
  }
  
  public function analysisLexical(
  ): void {
    $this->analysisLexicalTokens();
    calcTimer( "[PRE-CACHE] Criar Listagem de ExpressionWhere::Tokens" );
    $this->analysisLexicalOrigins();
    calcTimer( "[PRE-CACHE] Criar Listagem de ExpressionWhere::Origens" );
    $this->analysisLexicalCache();
    calcTimer( "[PRE-CACHE] Criar Listagem de ExpressionWhere::Cache" );
  }  

  public function get(
  ): void {
    $this->analysisLexical();
  }
};