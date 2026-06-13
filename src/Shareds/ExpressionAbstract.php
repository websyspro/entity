<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use ReflectionFunction;
use function ord, count, in_array, is_string, array_slice;

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

define( 'T_CONVERT_TO_BETWEEN', 1 );
define( 'T_CONVERT_TO_EQUAL', 2 );
define( 'T_CONVERT_TO_LIKE', 3 );
define( 'T_CONVERT_TO_IN', 4 );

class ExpressionAbstract
{
  public ExpressionType $expressionType;  
  public ReflectionFunction $reflectionFunction;
  public string $cacheClass;
  public string $cacheMethod;
  public array $scopes = [];
  public array $statics = [];
  public array $params = [];  
  public array $contexts = [];
  public array $tokens = [];
  public array $uses = [];

  public function __construct(
    public Closure $closure
  ){
    calcTimer( "Construtor da class ExpressionAbstract" );
    $this->analysisLexical();
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
      if( $token[0] === $type ){
        return $cursor;
      }
    }

    return -1;
  }
  
  public function isDenying(
    array $tokens = []
  ): bool {
    return $tokens[0][0] === T_NOT;
  }
  
  public function isGroup(
    array $tokens = []
  ): bool {
    return $tokens[0][0] === T_START_PARENTESES;
  }
  
  public function getSubQueryMethod(
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

  public function isSubQuery(
    array $contexts = []    
  ): bool {
    [ $subQueryMethod ] = $this->getSubQueryMethod($contexts);
    return in_array( $subQueryMethod[1], [ 'any' ]);
  }
  
  public function isLogical(
    array $tokens = []
  ): bool {
    [ $tokens ] = $tokens;
    [ $log ] = $tokens;
    return in_array( $log, [
      T_LOGICAL_AND, T_LOGICAL_OR, T_BOOLEAN_AND, T_BOOLEAN_OR 
    ]);
  }
  
  public function isCompare(
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
  
  public function isField(
    array $contexts = []
  ): bool {
    if( count( $contexts ) < 3 ){
      return false;
    }

    return $contexts[0][0] === T_VARIABLE
        && $contexts[1][0] === T_OBJECT_OPERATOR
        && $contexts[2][0] === T_STRING;
  }  

  public function analysisLexicalTokens(
  ): void {
    $this->reflectionFunction = new ReflectionFunction( $this->closure );
    if( $this->reflectionFunction instanceof ReflectionFunction ){
      $this->tokens = file( $this->reflectionFunction->getFileName());

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
  
  public function analysisLexicalOrigins(
  ): void {
    for( $i=count( $this->tokens ) - 1; $i>=0; $i-- ){
      if( strpos( $this->tokens[$i], 'function' ) !== false ){
        if( isset( $this->cacheMethod ) === false ){
          $this->cacheMethod = preg_replace([
            "#^.*function\s*#", "#\s*\(.*$#"
          ], "", trim( $this->tokens[ $i ]));
        }
      }
      
      if( strpos( $this->tokens[$i], 'class' ) !== false ){
        if(isset( $this->cacheClass ) === false ){
          $this->cacheClass = preg_replace([
            "#^.*class\s*#", "#\s*\{.*$#"
          ], "", trim( $this->tokens[ $i ]));
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
    [ $number, $value ] = is_string( $tokenArgs ) 
      ? [ ord( $tokenArgs ), $tokenArgs ] : $tokenArgs;

    if( in_array( $number, [ T_CONSTANT_ENCAPSED_STRING ])){
      $value = trim( $value, '"\'' );
    }  

    return [ $number, $value, $this->namberToken($number)];
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

    $this->contexts = $this->where(
      $this->contexts, fn(array $token) => (
        $token[0] !== T_WHITESPACE
      ) 
    );
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
      if($contexts[$i][0] === T_START_PARENTESES){
        $parenteses++;
      }

      if($contexts[$i][0] === T_END_PARENTESES){
        $parenteses--;

        if($parenteses < 0){
          $contexts = array_slice(
            $contexts, 0, $i
          ); break;
        }          
      }

      if($parenteses < 1){
        if($contexts[$i][0] === T_SEMICOLON){
          $contexts = array_slice(
            $contexts, 0, $i
          ); break;
        }
      }
    };

    return $contexts;
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
        $scope[1][1], $this->where(
          $this->uses, fn( array $use ) => $use[0] === $scope[0][1]
        )[0][1]
      ]
    );      
  }

  public function analysisLexicalScopes(
  ): void {
    $this->scopes = $this->analysisLexicalScopesExtracts( $this->contexts );
    $this->contexts = $this->contextsNotEnds(
      $this->slice( $this->contexts, $this->inc( $this->indexOf(
        $this->contexts, T_DOUBLE_ARROW
      )))
    );
  } 

  public function analysisLexicalHierarchySemanticsDenying(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    return [
      T_EXP_DENYING,
      $parent,
      $this->analysisLexicalHierarchySemantics(
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
      T_EXP_GROUP,
      $parent, $this->analysisLexicalHierarchySemantics( 
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
      T_EXP_SUBQUERY,
      $parent, $this->analysisLexicalHierarchySemantics(
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
     array $childs = []
  ): array {
    return [ 
      T_EXP_LOGICAL,
      match( $childs[0][0] ){
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
    return [ $scopeVariable[1], $fieldVariable[1] ];
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

    return [ $columnScheme, $columnType, $columnName ];
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
      [ T_OBJECT_OPERATOR], $this->slice( $childs, 4 )
    );

    $events = $this->mapper(
      $events, fn( array $tokens ) => [
        $tokens[0][1], in_array(
          $tokens[0][1], [ 'contains', 'startsWith', 'endsWith' ]
        ) ? 'compare' : 'modify', $this->groupByTypes(
          [ T_COMMA ], array_slice(
            $tokens, $this->inc(
              $this->indexOf(
                $tokens, T_START_PARENTESES
              )
            ), -1
          ), 
        )
      ]
    );

    return [ $events ];
  }

  public function createField(
    array $scopes,
    array $childs = []
  ): array {
    [ $variable, $column 
    ] = $this->fieldProps( $childs );
    
    return array_merge(
      $this->fieldPropByEntity( 
        $this->scopeByField( 
          $scopes, $variable 
        ), $column
      ), $this->fieldMethods( $childs )
    );
  }
  
  public function createEqual(
    array $childs = []
  ): array {
    return $childs;
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
  
  public function analysisLexicalHierarchySemanticsCompare(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    [ $childsLeft, $childsEqual, $childsRight 
    ] = $this->groupByTypes([ 
      T_EQUAL,
      T_IS_EQUAL, T_IS_IDENTICAL,
      T_IS_NOT_EQUAL, T_IS_NOT_IDENTICAL,
      T_IS_GREATER_OR_EQUAL, T_IS_SMALLER_OR_EQUAL,
      T_GREATER_THAN, T_LESS_THAN 
    ], $childs, true );

    [ $childsLeft, $childsEqual, $childsRight ] = [
      $this->isField( $childsLeft ) 
        ? [ T_EXP_FIELD, ...$this->createField( $scopes, $childsLeft )]
        : [ T_EXP_VALUE, $childsLeft ],
          [ T_EXP_EQUAL, ...$this->createEqual( $childsEqual )],
      $this->isField( $childsRight ) 
        ? [ T_EXP_FIELD, ...$this->createField( $scopes, $childsRight )] 
        : [ T_EXP_VALUE, $childsRight ]  
    ];

    if( $childsLeft[0] === T_EXP_VALUE ){
      $childsEqual = $this->reverseEqual( $childsEqual );

      return [ T_EXP_COMPARE, $parent, [ 
        $childsRight, [ T_EXP_EQUAL, $childsEqual[1][1] ], $childsLeft 
      ]];
    } else {
      return [ T_EXP_COMPARE, $parent, [
        $childsLeft, [ T_EXP_EQUAL, $childsEqual[1][1] ], $childsRight
      ]];
    }
  }
  
  public function analysisLexicalHierarchySemanticsUnary(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    return [ 
      T_EXP_UNARY,
      $parent, [ T_EXP_FIELD, ...$this->createField( $scopes, $childs )]
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

  public function analysisLexicalHierarchySimpleSemanticsToBetween(
    array &$childs,
    int $x, $y
  ): void {
    if( $childs[$x][2][0][0] === T_EXP_FIELD ){
      if( $childs[$y][2][0][0] === T_EXP_FIELD ){
        if( $childs[$x][2][0][1] === $childs[$y][2][0][1] ){
          if( $childs[$x][2][0][3] === $childs[$y][2][0][3] ){
            if( $childs[$x][2][0][2] === "Datetime" && $childs[$y][2][0][2] === "Datetime" ){
              if( $childs[$y - 1][1] === "And" ){
                $childs[$x][2][0][4][] = [
                  "date", "modify", []
                ];

                $childs[$x] = [
                  T_EXP_BETWEEN, $childs[$x][1], [
                    $childs[$x][2][0], $childs[$x][2][2], $childs[$y][2][2]
                  ]
                ];

                $y !== 0
                  ? array_splice( $childs, $y - 1, 2 ) 
                  : array_splice( $childs, $y, 1 );
              }
            }
          }
        }
      }
    };
  }

  public function analysisLexicalHierarchySimpleSemanticsToLike(
    array &$childs,
    int $x
  ): void {
    if( $childs[$x][0] === T_EXP_UNARY ){
      if( $childs[$x][2][2] === "Flag" ){
        $childs[$x][0] = T_EXP_COMPARE;
        $childs[$x][2][] = [ T_EXP_EQUAL, "=" ];
        $childs[$x][2][] = [ 
          T_EXP_VALUE,
          $childs[$x][1] === T_EXP_DENYING 
            ? [ 313, "false", token_name( 313 )] 
            : [ 313, "true", token_name( 313 )]
        ];
      }
    }
  }  

  public function analysisLexicalHierarchySimpleSemanticsToEqual(
    array &$childs,
    int $x
  ): void {
    if( $childs[$x][0] === T_EXP_UNARY ){
      if( $childs[$x][2][2] === "Text" ){
        //$childs[$x][0] = T_EXP_COMPARE;
      }
    }
  }  

  public function analysisLexicalHierarchySimpleSemantics(
    int $convertType,
    array $contexts = []
  ): array {
    if( $convertType === T_CONVERT_TO_BETWEEN ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][0], [ T_EXP_COMPARE ])){
          for( $y = $x + 1; $y < count($contexts); $y++ ){
            if( in_array( $contexts[$y][0], [ T_EXP_COMPARE ])){
              $this->analysisLexicalHierarchySimpleSemanticsToBetween( $contexts, $x, $y );
            };      
          }
        }
      }
    } else
    if( $convertType === T_CONVERT_TO_EQUAL ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][0], [ T_EXP_UNARY ])){
          $this->analysisLexicalHierarchySimpleSemanticsToLike( $contexts, $x );
        }
      }
    } else
    if( $convertType === T_CONVERT_TO_LIKE ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][0], [ T_EXP_UNARY ])){
          $this->analysisLexicalHierarchySimpleSemanticsToEqual( $contexts, $x );
        }
      }
    }
    

    return $this->analysisLexicalHierarchySimples( $contexts );
  }

  public function analysisLexicalHierarchySimples(
    array $contexts = []
  ): array {
    return $this->mapper( 
      $contexts, function( array $childs ){
        if( in_array( $childs[0], [ T_EXP_GROUP, T_EXP_DENYING, T_EXP_SUBQUERY ])){
          $childs[2] = $this->analysisLexicalHierarchySimpleSemantics( T_CONVERT_TO_BETWEEN, $childs[ 2 ]);
          $childs[2] = $this->analysisLexicalHierarchySimpleSemantics( T_CONVERT_TO_EQUAL, $childs[ 2 ]);
          $childs[2] = $this->analysisLexicalHierarchySimpleSemantics( T_CONVERT_TO_LIKE, $childs[ 2 ]);
        }

        return $childs;
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
    unset( $this->reflectionFunction );
  }  
  
  public function analysisLexical(
  ): void {
    $this->analysisLexicalTokens();
    calcTimer( "Criar Listagem de ExpressionWhere::Tokens" );
    $this->analysisLexicalUses();
    calcTimer( "Criar Listagem de ExpressionWhere::Uses" );
    $this->analysisLexicalOrigins();
    calcTimer( "Criar Listagem de ExpressionWhere::Origens" );
    $this->analysisLexicalContexts();
    calcTimer( "Criar Listagem de ExpressionWhere::Contexts" );
    $this->analysisLexicalScopes();
    calcTimer( "Criar Listagem de ExpressionWhere::Scopes" );
    $this->analysisLexicalHierarchy();
    calcTimer( "Criar Listagem de ExpressionWhere::Hierarchy" );
    $this->analysisLexicalSimples();
    calcTimer( "Criar Listagem de ExpressionWhere::Simples" );    
    $this->analysisLexicalClear();
    calcTimer( "Criar Listagem de ExpressionWhere::Clear" );
  }  
}