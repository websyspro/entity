<?php

namespace Websyspro\Entity\Shareds;

use function in_array, count;

class ExpressionWhere
extends Utils
{
  public array $contexts = [];

  public function __construct(
    public string $signary,
    public string $hash,
    public array $statements = [],
    public array $scopes = [],
    public array $tokens = [],
  ){}

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
        $scope[1][T_TOKEN_VALUE], $this->filter(
          $this->statements, fn( array $use ) => $use[0] === $scope[0][T_TOKEN_VALUE]
        )[0][1]
      ]
    );      
  }  

  public function analysisLexicalHierarchyApplyDenying(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    if( $this->getExpType( $this->slice( $childs, 1 )) === T_EXP_UNARY ){
      if( count( $this->slice( $childs, 1 )) === 3 ){
        return $this->analysisLexicalHierarchyApplyCompare(
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
      T_CHILDS => $this->analysisLexicalHierarchyApply(
        T_EXP_DENYING, $scopes, $this->slice( $childs, 1 )
      )
    ];
  }

  public function analysisLexicalHierarchyApplyGroup(
    string $parent,
    array $scopes,
    array $childs = []
  ): array {
    return [ 
      T_OBJECT => T_EXP_GROUP,
      T_PARENT => $parent, 
      T_CHILDS => $this->analysisLexicalHierarchyApply( 
        T_EXP_GROUP, $scopes, $this->slice( $childs, 1, -1 )
      )
    ];
  }
  
  public function analysisLexicalHierarchyApplySubQuery(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    return [
      T_OBJECT => T_EXP_SUBQUERY,
      T_PARENT => $parent,
      T_CHILDS => $this->analysisLexicalHierarchyApply(
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
  
  public function analysisLexicalHierarchyApplyLogical(
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
  
  public function analysisLexicalHierarchyApplyCompare(
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
  
  public function analysisLexicalHierarchyApplyUnary(
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

  public function analysisLexicalHierarchyApply(
    string $parent,
    array $scopes,
    array $contexts = []
  ): array {
    return $this->mapper( 
      $this->groupByTypesLogical( $contexts ), fn( array $childs ) => (
        match( $this->getExpType( $childs )){
          T_EXP_DENYING => $this->analysisLexicalHierarchyApplyDenying( $parent, $scopes, $childs ),
          T_EXP_GROUP => $this->analysisLexicalHierarchyApplyGroup( $parent, $scopes, $childs ),
          T_EXP_SUBQUERY => $this->analysisLexicalHierarchyApplySubQuery( $parent, $scopes, $childs ),
          T_EXP_LOGICAL => $this->analysisLexicalHierarchyApplyLogical( $childs ),
          T_EXP_COMPARE => $this->analysisLexicalHierarchyApplyCompare( $parent, $scopes, $childs ),
          T_EXP_UNARY => $this->analysisLexicalHierarchyApplyUnary( $parent, $scopes, $childs ),
            default => $childs
        }
      )
    );
  }  
  
  public function analysisLexicalHierarchy(
  ): void {
    $this->contexts = $this->analysisLexicalHierarchyApply( 
      T_EXP_INITIAL, $this->scopes, $this->tokens
    );
  }

  public function analysisLexicalSemanticsApplyActionToAjustSide(
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

  public function analysisLexicalSemanticsApplyActionToAjustEquals(
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
  
  public function analysisLexicalSemanticsApplyActionToMethods(
    array &$contexts, int $x,
    array $contextsLinks = [],
    array $contextsArgs = []
  ): void {
    if($contexts[$x][T_CHILDS][0][T_OBJECT] === T_EXP_FIELD){
      if( empty( $contexts[$x][T_CHILDS][0][T_COLUMN_METHODS]) === false ){
        $methodCompare = $this->filter( 
          $contexts[$x][T_CHILDS][0][T_COLUMN_METHODS], 
            fn(array $method) => $method[T_COLUMN_METHOD_TYPE] === "compare" 
        );

        $contexts[$x][T_CHILDS][0][T_COLUMN_METHODS] = $this->filter( 
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

  public function analysisLexicalSemanticsApplyActionToBetween(
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

  public function analysisLexicalSemanticsApplyActionToLike(
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

  public function analysisLexicalSemanticsApplyAction(
    int $convertType,
    array $contexts = []
  ): array {
    if( $convertType === T_ACTION_TO_ADJUST_SIDE ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][T_OBJECT], [ T_EXP_COMPARE ])){
          $this->analysisLexicalSemanticsApplyActionToAjustSide( $contexts, $x );
        }
      }
    } else
    if( $convertType === T_ACTION_TO_ADJUST_EQUALS ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][T_OBJECT], [ T_EXP_COMPARE ])){
          $this->analysisLexicalSemanticsApplyActionToAjustEquals( $contexts, $x );
        }
      }
    }
    if( $convertType === T_ACTION_TO_METHODS ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][T_OBJECT], [ T_EXP_UNARY, T_EXP_COMPARE ])){
          $this->analysisLexicalSemanticsApplyActionToMethods( $contexts, $x );
        }
      }
    } else    
    if( $convertType === T_ACTION_TO_BETWEEN ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][T_OBJECT], [ T_EXP_COMPARE ])){
          for( $y = $x + 1; $y < count($contexts); $y++ ){
            if( in_array( $contexts[$y][T_OBJECT], [ T_EXP_COMPARE ])){
              $this->analysisLexicalSemanticsApplyActionToBetween( $contexts, $x, $y );
            };      
          }
        }
      }
    } else
    if( $convertType === T_ACTION_TO_LIKE ){
      for( $x = 0; $x < count( $contexts ); $x++ ){
        if( in_array( $contexts[$x][T_OBJECT], [ T_EXP_UNARY, T_EXP_COMPARE ])){
          $this->analysisLexicalSemanticsApplyActionToLike( $contexts, $x );
        }
      }
    }

    return $this->analysisLexicalSemanticsApply( $contexts );
  }  

  public function analysisLexicalSemanticsApply(
    array $contexts = []
  ): array {
    return $this->mapper( 
      $contexts, function(array $context){
        if( in_array($context[T_OBJECT], [T_EXP_GROUP, T_EXP_DENYING, T_EXP_SUBQUERY])){
          $context[T_CHILDS] = $this->analysisLexicalSemanticsApplyAction( T_ACTION_TO_ADJUST_SIDE, $context[T_CHILDS]);
          $context[T_CHILDS] = $this->analysisLexicalSemanticsApplyAction( T_ACTION_TO_ADJUST_EQUALS, $context[T_CHILDS]);
          $context[T_CHILDS] = $this->analysisLexicalSemanticsApplyAction( T_ACTION_TO_METHODS, $context[T_CHILDS]);
          $context[T_CHILDS] = $this->analysisLexicalSemanticsApplyAction( T_ACTION_TO_BETWEEN, $context[T_CHILDS]);
          $context[T_CHILDS] = $this->analysisLexicalSemanticsApplyAction( T_ACTION_TO_LIKE, $context[T_CHILDS]);
        }

        return $context;
      }
    );
  }
  
  public function analysisLexicalSemantics(
  ): void {
    $this->contexts = $this->analysisLexicalSemanticsApply( $this->contexts );
  }

  public function analysisLexicalSave(
  ): void {
    Cache::save( "orm-where-{$this->signary}", [
      T_HASH => $this->hash,
      T_CONTEXTS => $this->contexts
    ]);
  }

  public function analysisLexical(
  ): void {
    $this->analysisLexicalHierarchy();
    $this->analysisLexicalSemantics();
    $this->analysisLexicalSave();
}

  public function analysisLexicalInitial(
  ): void {
    calcTimer( "Start Load Cache Cache::getWhereOrNull" );
    $cache = Cache::getWhereOrNull( $this->signary );
    calcTimer( "Load Cache Cache::getWhereOrNull" );
    if( $cache === false || $cache[T_HASH] !== $this->hash ){
      $this->analysisLexical();
    } else {
      $this->contexts = $cache[T_CONTEXTS];
    }    
  }  
}