<?php

namespace Websyspro\Entity\Shareds;

class ExpressionWhere
extends ExpressionAbstract
{
  public function analysisSemanticsDenying(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    return [
      T_EXP_DENYING,
      $parent,
      $this->analysisSemantics(
        T_EXP_DENYING, $scopes, $this->slice( $childs, 1 )
      )
    ];
  }

  public function analysisSemanticsGroup(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    return [ 
      T_EXP_GROUP,
      $parent, $this->analysisSemantics( 
        T_EXP_GROUP, $scopes, $this->slice( $childs, 1, -1 )
      )
    ];
  } 
  
  public function analysisSemanticsSubQuery(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    return [
      T_EXP_SUBQUERY,
      $parent, $this->analysisSemantics(
        T_EXP_SUBQUERY, array_merge( 
          $scopes, $this->analysisLexicalScopesExtracts(
            $this->slice( $childs, $this->indexOf( $childs, T_FN ))
          )
        ), $this->contextsNotEnds(
          $this->slice( $childs, $this->inc( $this->indexOf( $childs, T_DOUBLE_ARROW )))
        )
      )
    ];
  }

  public function analysisSemanticsLogical(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    return [ 
      T_EXP_LOGICAL,
      $parent, $scopes, $childs
    ];
  }

  public function analysisSemanticsCompareField(
    array $scopes,
    array $childs = []
  ): array {
    [ $scopeVariable, $fieldVariable ] = $this->fieldProps( $childs );
    [ $entity, $types ] = $this->fieldPropByEntity( 
      $this->scopeByField( $scopes, $scopeVariable )
    );

    return [
      $entity[ 0 ],
      $types[ $fieldVariable ], 
      $fieldVariable
    ];
  }
  
  public function analysisSemanticsCompare(
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
        ? [ T_EXP_FIELD, $this->analysisSemanticsCompareField( $scopes, $childsLeft )]
        : [ T_EXP_VALUE, $childsLeft ], [ T_EXP_EQUAL, $childsEqual ],
      $this->isField( $childsRight ) 
        ? [ T_EXP_FIELD, $this->analysisSemanticsCompareField( $scopes, $childsRight )] 
        : [ T_EXP_VALUE, $childsRight ]  
    ];

    return [ 
      T_EXP_COMPARE,
      $parent, $scopes, [ $childsLeft, $childsEqual, $childsRight ]
    ];
  }
  
  public function analysisSemanticsUnary(
    string $parent,
     array $scopes,
     array $childs = []
  ): array {
    return [ 
      T_EXP_UNARY,
      $parent, $scopes, [ T_EXP_FIELD, $this->analysisSemanticsCompareField( $scopes, $childs ) ]
    ];
  }  

  public function analysisSemantics(
    string $parent,
     array $scopes,
     array $contexts = []
  ): array {
    $contexts = $this->groupByTypes(
      [ T_LOGICAL_AND, T_LOGICAL_OR, T_BOOLEAN_AND, T_BOOLEAN_OR 
      ], $contexts, true
    );

    return $this->mapper( $contexts, fn( array $childs ) => (
      match( $this->getExpType( $childs )){
        T_EXP_DENYING => $this->analysisSemanticsDenying( $parent, $scopes, $childs ),
        T_EXP_GROUP => $this->analysisSemanticsGroup( $parent, $scopes, $childs ),
        T_EXP_SUBQUERY => $this->analysisSemanticsSubQuery( $parent, $scopes, $childs ),
        T_EXP_LOGICAL => $this->analysisSemanticsLogical( $parent, $scopes, $childs ),
        T_EXP_COMPARE => $this->analysisSemanticsCompare( $parent, $scopes, $childs ),
        T_EXP_UNARY => $this->analysisSemanticsUnary( $parent, $scopes, $childs ),
          default => $childs
      })
    );
  }

  public function analysisLexicalInit(
  ): void {
    $this->contexts = $this->analysisSemantics( 
      T_EXP_INITIAL, $this->scopes, $this->contexts
    );
  }  
}