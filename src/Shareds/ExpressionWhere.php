<?php

namespace Websyspro\Entity\Shareds;

use function in_array, count;

class ExpressionWhere
extends Utils
{
  public array $contexts = [];
  public array $params = [];

  public function __construct(
    public string $signary,
    public string $hash,
    public int $signaryId,
    public array $statements,
    public array $statics,
    public array $scopes,
    public array $tokens,
    public ExpressionType $expressionType
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
        K_STATEMENTS => $this->filter(
          $this->statements, fn( array $statement ) => (
            $statement[K_VARIABLE] === $scope[0][T_TOKEN_VALUE]
          )
        )[0][K_STATEMENTS],
        K_VARIABLE => $scope[1][T_TOKEN_VALUE]
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
    
    $isGroups = in_array(
      $this->getExpType( $this->slice( $childs, 1 )), [
        T_EXP_GROUP, T_EXP_SUBQUERY
      ]
    );

    return [ 
      T_OBJECT => T_EXP_DENYING, 
      T_PARENT => $parent,
      T_CHILDS => $isGroups 
        ? $this->analysisLexicalHierarchyApply( T_EXP_DENYING, $scopes, $this->slice( $childs, 1 )) 
        : [ $this->analysisLexicalHierarchyApplyUnary( T_EXP_DENYING, $scopes, $this->slice( $childs, 1 ))]
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
      T_METHOD => $this->getSubQueryMethod( $childs ), 
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

    $childs = [
      $this->isField( $childA ) 
        ? $this->createField( $scopes, $childA ) 
        : $this->createValue( $childA ), $this->createEqual( $equals ),
      $this->isField( $childB )
        ? $this->createField( $scopes, $childB ) 
        : $this->createValue( $childB )
    ];

    if( $childs[0][T_OBJECT] === T_EXP_FIELD && $childs[2][T_OBJECT] === T_EXP_VALUE ){
      $childs[2][T_VALUES_TYPE] = $childs[0][T_COLUMN_TYPE];
    } else if( $childs[2][T_OBJECT] === T_EXP_FIELD && $childs[0][T_OBJECT] === T_EXP_VALUE ){
      $childs[0][T_VALUES_TYPE] = $childs[2][T_COLUMN_TYPE];
    }

    return [ T_OBJECT => T_EXP_COMPARE, T_PARENT => $parent, T_CHILDS => $childs ];
  }
  
  public function analysisLexicalHierarchyApplyUnary(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    $field = $this->createField( $scopes, $childs );
    if( $field[ T_COLUMN_TYPE ] !== "Flag" ){
      return [ 
        T_OBJECT => T_EXP_UNARY,
        T_PARENT => $parent,
        T_CHILDS => [ $field ]
      ];
    } else {
      return $this->analysisLexicalHierarchyApplyCompare(
        $parent, $scopes,array_merge( 
          $childs, 
          [ $this->createToken([ T_IS_IDENTICAL, "===" ]) ],
          [ $this->createToken([ T_STRING, $parent === T_EXP_DENYING ? "false" : "true" ]) ]
        )
      );
    }
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
            default => $this->analysisLexicalHierarchyApply( $parent, $scopes, $childs )
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
                      ),
                      T_VALUES_TYPE => $contexts[$x][T_CHILDS][0][T_COLUMN_TYPE]
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
                      ),
                      T_VALUES_TYPE => $contexts[$x][T_CHILDS][0][T_COLUMN_TYPE]
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
                      T_OBJECT => T_EXP_VALUE, 
                      T_VALUES => array_merge(
                        [ $this->createToken([ T_STRING, '%' ]) ], [
                          $methodCompare[0][T_COLUMN_METHOD_ARGS][$y]
                        ]
                      ),
                      T_VALUES_TYPE => $contexts[$x][T_CHILDS][0][T_COLUMN_TYPE]
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
                  $contextsLinks[$y][T_PARENT] = $contexts[$x][T_PARENT] === T_EXP_DENYING 
                    ? T_EXP_DENYING : T_EXP_GROUP;
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
                  ),
                  T_VALUES_TYPE => $contexts[$x][T_CHILDS][0][T_COLUMN_TYPE]
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
              T_CHILDS => $contexts[$x][T_CHILDS]
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
            T_CHILDS => [
              $contexts[$x][T_CHILDS][0], [
                T_OBJECT => T_EXP_VALUE, 
                T_VALUES => $contexts[$x][T_CHILDS][2][T_VALUES] 
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

  public function analysisLexicalSemanticsApplyInChilds(
    array $childs = []
  ): array {
    $childs = $this->analysisLexicalSemanticsApplyAction( T_ACTION_TO_ADJUST_SIDE, $childs);
    $childs = $this->analysisLexicalSemanticsApplyAction( T_ACTION_TO_ADJUST_EQUALS, $childs);
    $childs = $this->analysisLexicalSemanticsApplyAction( T_ACTION_TO_METHODS, $childs);
    $childs = $this->analysisLexicalSemanticsApplyAction( T_ACTION_TO_BETWEEN, $childs);
    $childs = $this->analysisLexicalSemanticsApplyAction( T_ACTION_TO_LIKE, $childs);    
    return $childs;
  }

  public function isSemanticsChilds(
    array $contexts = []
  ): bool {
    return in_array(
      $contexts[ T_OBJECT ], [
        T_EXP_GROUP, T_EXP_DENYING, T_EXP_SUBQUERY 
      ]
    );
  }

  public function analysisLexicalSemanticsApply(
    array $contexts = []
  ): array {
    $contexts = $this->mapper( 
      $contexts, function( array $context ){
        if( $this->isSemanticsChilds( $context )){
          $context[T_CHILDS] = $this->analysisLexicalSemanticsApplyInChilds(
            $context[T_CHILDS]
          );
        }

        return $context;
      }
    );

    return $contexts;
  }
  
  public function analysisLexicalSemantics(
  ): void {
    $this->contexts = $this->analysisLexicalSemanticsApply( 
      $this->isSemanticsChilds( $this->contexts[ 0 ]) === false 
        ? $this->analysisLexicalSemanticsApplyInChilds( $this->contexts ) 
        : $this->contexts 
    );
  }

  public function analysisLexicalSave(
  ): void {
    Cache::save( "orm-where-{$this->signary}", [
      T_HASH => $this->hash,
      T_CONTEXTS => $this->contexts
    ]);
  }

  public function analysisLexical(
  ): array {
    $this->analysisLexicalHierarchy();
    $this->analysisLexicalSemantics();
    $this->analysisLexicalSave();
    return [ T_CONTEXTS => $this->contexts ]; 
  }

  public function analysisValuesApplyInChildAlls(
    array $childs = []
  ): array {
    for($i=1; $i < count($childs[T_CHILDS]); $i++){
      if($childs[T_CHILDS][$i][T_OBJECT] === T_EXP_VALUE){
        $childs[T_CHILDS][$i][T_VALUES] = $this->variableToStatic(
          $childs[T_CHILDS][$i][T_VALUES], $this->statics
        );
        
        $childs[T_CHILDS][$i][T_VALUES] = $this->enumToStatic(
          $childs[T_CHILDS][$i][T_VALUES], $this->statements
        );
        
        $childs[T_CHILDS][$i][T_VALUES] = $this->staticToParam( 
          $childs[T_CHILDS][$i][T_VALUES], 
          $childs[T_CHILDS][$i][T_VALUES_TYPE], $this
        );
      }
    }

    return $childs;
  }

  public function analysisValuesApplyInChild(
    array $childs = []
  ): array {
    if( $childs[ T_OBJECT ] !== T_EXP_LOGICAL ){
      $childs = $this->analysisValuesApplyInChildAlls( $childs ); 
    }

    return $childs;
  }

  public function analysisValuesApply(
    array $contexts = []
  ): array {
    return $this->mapper( 
      $contexts, fn( array $context ) =>
        $context[ T_CHILDS ] = $this->isSemanticsChilds( $context )
          ? $this->analysisValuesApply( $context[ T_CHILDS ]) 
          : $this->analysisValuesApplyInChild( $context )
    );
  }  

  public function analysisValues(
  ): void {
    $this->contexts = $this->analysisValuesApply($this->contexts);
  } 

  public function analysisLexicalFromCache(
  ): array {
    return Cache::load( "orm-where-{$this->signary}" );
  }  

  public function analysisLexicalExistCache(
  ): string {
    if( Cache::exist( "orm-where-{$this->signary}" )){
      [ T_HASH => $hash ] = $this->analysisLexicalFromCache();
      return $this->hash === $hash;
    } 

    return false;
  }  

  public function analysisLexicalInitial(
  ): array {
    [ T_CONTEXTS => $this->contexts ] = $this->analysisLexicalExistCache() 
      ? $this->analysisLexicalFromCache()
      : $this->analysisLexical();

    //$this->analysisValues();
    return [ $this->contexts, $this->params ];
  }  
}