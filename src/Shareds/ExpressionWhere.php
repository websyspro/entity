<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use ReflectionFunction;
use function sprintf, is_array;

class ExpressionWhere
extends ExpressionUtil
{
  public ReflectionFunction $reflectionFunction;
  public array $context = [];

  public function __construct(
    Closure $closure
  ){
    $closure = ClosureUtil::addClosure( $closure);
    $this->preCompile( $closure );
  }

  private function preCompile(
    Closure $closure
  ): void {
    $tokens = $this->tokensAll( $closure );

    if( $this->existsCache( $tokens )){
      $this->context = $this->loadCache();
    } else {
      $this->context = $this->createExpressionNode(
        $this->getContext( $tokens ), $this->getScopes( $tokens, $closure ), $closure
      );
  
      if( empty( $this->context ) === false ){
        $this->saveCache( $tokens, $this->context );
      } 
    }
  }  

  private function createExpressionNode(
    array $tokens,
    array $scopes,
    Closure $closure
  ): array {
    $tokens = $this->preCompileParserTokens( $tokens );
    $tokens = $this->preCompileTokens( $tokens, $scopes, $closure );
    $tokens = $this->preCompileImplodeTokens( $tokens );
    $tokens = $this->preCompileRevaliderTokens( $tokens );

    return [
      T_KEY_OBJECT => T_EXPRESSION_NODE,
      T_KEY_TOKENS => $tokens
    ];
  }

  private function createExpressionNegative(
    array $tokens,
    array $scopes,
    Closure $closure
  ): array {
    $tokens = $this->dropNegative( $tokens );
    if( $this->getExpressionType( $tokens ) === T_IS_EXPRESSION_SUBQUERY ){
      $tokens = $this->dropNegative( $tokens );
      $tokens = $this->preCompileParserTokens( $tokens );
      $tokens = $this->preCompileTokens( $tokens, $scopes, $closure );

      return [
        T_KEY_OBJECT => T_EXPRESSION_NEGATIVE,
        T_KEY_TOKENS => $tokens
      ];
    }
    
    $tokens = $this->createTokenCompareEqualIsNegatiive( $tokens );
    return $this->createExpressionCompare( $tokens, $scopes, $closure);
  }  

  private function createExpressionGroup(
    array $tokens,
    array $scopes,
    Closure $closure
  ): array {
    $tokens = $this->dropParenteses( $tokens );
    $tokens = $this->preCompileParserTokens( $tokens );
    $tokens = $this->preCompileTokens( $tokens, $scopes, $closure );
    $tokens = $this->preCompileImplodeTokens( $tokens );
    $tokens = $this->preCompileRevaliderTokens( $tokens );

    return [
      T_KEY_OBJECT => T_EXPRESSION_GROUP,
      T_KEY_TOKENS => $tokens
    ];
  }
  
  private function createExpressionSubQuery(
    array $tokens,
    array $scopes,
    Closure $closure
  ): array {
    $events = $this->getEventBySubQuery( $tokens );
    $tokens = $this->dropInitialInvalids( $tokens );
    $scopes = $this->getScopes( $tokens, $closure, $scopes );
    $tokens = $this->getContext( $tokens );
    $tokens = $this->dropEndInvalids( $tokens );
    $tokens = $this->preCompileParserTokens( $tokens );
    $tokens = $this->preCompileTokens( $tokens, $scopes, $closure );
    $tokens = $this->preCompileImplodeTokens( $tokens );
    $tokens = $this->preCompileRevaliderTokens( $tokens );
    $entity = $this->entitySubQuery( $scopes );

    return [ 
      T_KEY_OBJECT => T_EXPRESSION_SUBQUERY,
      T_KEY_ENTITY => $entity,
      T_KEY_METHODS => $events,
      T_KEY_TOKENS => $tokens
    ];
  }
  
  private function createExpressionLogical(
    array $tokens
  ): array {
    $token = $this->adjustCompare( $tokens );
    
    return [
      T_KEY_OBJECT => T_EXPRESSION_LOGICAL,
      T_KEY_TOKENS => $token
    ];
  }

  private function createExpressionUnary(
    array $tokens,
    array $scopes,
    Closure $closure
  ): array {
    $tokens = $this->createTokenCompareEqualIsNotNegatiive( $tokens );
    return $this->createExpressionCompare( $tokens, $scopes, $closure);
  }
  
  private function createExpressionCompare(
    array $tokens,
    array $scopes,
    Closure $closure
  ): array {
    $tokens = $this->parserTokensCompare( $tokens );
    $tokens = $this->loopCompareTokens( $closure, $scopes, $tokens );
    $tokens = $this->adjustComparePositions( $tokens );
    // $tokens = $this->adjustCompareParserValue( $closure, $tokens );

    return [
      T_KEY_OBJECT => T_EXPRESSION_COMPARE,
      T_KEY_TOKENS => $tokens
    ];
  }  

  private function preCompileTokens(
    array $tokens,
    array $scopes,
    Closure $closure,
  ): array {
    foreach($tokens as $key => $token){
      $tokens[$key] = match( $this->getExpressionType( $token )){
        T_IS_EXPRESSION_NEGATIVE => $this->createExpressionNegative( $token, $scopes, $closure ),
        T_IS_EXPRESSION_GROUP => $this->createExpressionGroup( $token, $scopes, $closure ),
        T_IS_EXPRESSION_SUBQUERY => $this->createExpressionSubQuery( $token, $scopes, $closure ),  
        T_IS_EXPRESSION_LOGICAL => $this->createExpressionLogical( $token ),
        T_IS_EXPRESSION_UNARY => $this->createExpressionUnary( $token, $scopes, $closure ),
        T_IS_EXPRESSION_COMPARE => $this->createExpressionCompare( $token, $scopes, $closure )
      };
    }

    return $tokens;
  }

  private function createExpressionField(
    array $tokens,
    array $scopes,
    Closure $closure
  ): array {
    [ $table, $field, $type, $methods 
    ] = $this->expressionFieldProps( $tokens, $scopes, $closure );
    
    return [
      T_KEY_OBJECT => T_EXPRESSION_FIELD,
      T_KEY_ENTITY => $table,
      T_KEY_FIELD => $field,
      T_KEY_TYPE => $type,
      T_KEY_METHODS => $methods
    ];
  }

  private function createExpressionEqual(
    array $tokens
  ): array {
    [ $token ] = $tokens;
    
    return [
      T_KEY_OBJECT => T_EXPRESSION_EQUAL,
      T_KEY_TOKENS => $token
    ];
  }  

  private function createExpressionValue(
    Closure $closure,
    array $tokens
  ): array {
    $isList = $this->isExpressionValueList( $tokens );
    $tokens = $this->dropCurlOpenAndNotDot( $tokens );
    // $tokens = $this->updateTokensVariable( $tokens, $closure );
    // $tokens = $this->updateTokensEnums( $tokens, $closure );
    // $tokens = $this->adjustValues( $tokens );

    return [
      T_KEY_OBJECT => T_EXPRESSION_VALUE,
      T_KEY_ISLIST => $isList,
      T_KEY_TOKENS => $tokens
    ];
  }  
  
  private function loopCompareTokens(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    foreach($tokens as $key => $token){
      $tokens[ $key ] = match( $this->getExpressionCompareType( $token )){
        T_IS_EXPRESSION_FIELD => $this->createExpressionField( $token, $scopes, $closure ),
        T_IS_EXPRESSION_EQUAL => $this->createExpressionEqual( $token),
        T_IS_EXPRESSION_VALUE => $this->createExpressionValue( $closure, $token )
      };
    }

    return $tokens;
  }
  
  private function buildField(
    array $tokensFromField
  ): string {
    [ T_KEY_ENTITY => $entity, T_KEY_FIELD => $field, T_KEY_METHODS => $methods
    ] = $tokensFromField;

    return "{$entity}.{$field}";
  }

  private function buildExpressionLogical(
    array $expressionLogical
  ): string {
    return $expressionLogical[T_KEY_TOKENS];
  }  

  private function buildExpressionCompare(
    array $expressionCompare
  ): string|null {
    $expressionCompareType = $this->expressionCompareType($expressionCompare);
 
    if( $expressionCompareType === T_FIELD_AND_VALUE ){
      [ $expressionField, $expressionEqual, $expressionValue 
      ] = $expressionCompare[ T_KEY_TOKENS ];

      return sprintf( "%s %s %s", $this->buildField( $expressionField ), $expressionEqual[ T_KEY_VALUE ], $expressionValue[ T_KEY_VALUE ]);
    } else
    if( $expressionCompareType === T_FIELD_AND_FIELD ){
      [ $expressionField1, $expressionEqual, $expressionField2 
      ] = $expressionCompare[ T_KEY_TOKENS ];
      
      return "{$this->buildField( $expressionField1 )} {$expressionEqual[T_KEY_VALUE]} {$this->buildField( $expressionField2 )}";
    }

    return null;
  }

  private function buildExpressionIn(
    array $expressionIn
  ): string {
    [ $expressionField, $expressionValue ] = $expressionIn[ T_KEY_TOKENS ];
    return "{$this->buildField($expressionField)} In {$expressionValue[T_KEY_VALUE]}";
  }

  private function buildExpressionNotIn(
    array $expressionNotIn
  ): string {
    [ $expressionField, $expressionValue ] = $expressionNotIn[T_KEY_TOKENS];
    return "{$this->buildField($expressionField)} Not In {$expressionValue[ T_KEY_VALUE ]}";
  }  

  private function buildExpressionLike(
    array $expressionLike
  ): string {
    [ $expressionField, $expressionValue ] = $expressionLike[T_KEY_TOKENS];
    return is_array( $expressionValue[ T_KEY_VALUE ])
      ? sprintf( "%s Like %s", $this->buildField($expressionField), join( "", $expressionValue[ T_KEY_VALUE ]))
      : sprintf( "%s Like %s", $this->buildField($expressionField), join( "", $expressionValue[ T_KEY_VALUE ]));
  }

  private function buildExpressionNotLike(
    array $expressionNotLike  
  ): string {
    [ $expressionField, $expressionValue 
    ] = $expressionNotLike[ T_KEY_TOKENS ];

    return "{$this->buildField($expressionField)} Not Like {$expressionValue[T_KEY_VALUE][0]}";
  }
  
  private function buildExpressionNegative(
    array $expressionNegative
  ): string {
    [ $expressionChild ] = $expressionNegative[ T_KEY_TOKENS ];
    
    if( $expressionChild[ T_KEY_OBJECT ] === T_EXPRESSION_SUBQUERY ){
      return "Not {$this->buildLoop($expressionChild)}";
    } else return "{$this->buildLoop($expressionChild)}";
  }

  private function buildExpressionGroup(
    array $expressionGroup
  ): string {
    return "({$this->buildLoop($expressionGroup[T_KEY_TOKENS])})";
  }  

  private function buildExpressionBetween(
    array $expressionBetween
  ): string {
    [ $expressionField, $expressionValueStart, $expressionValueEnd 
    ] = $expressionBetween[ T_KEY_TOKENS ];

    return "{$this->buildField($expressionField)} Between {$expressionValueStart[T_KEY_VALUE]} And {$expressionValueEnd[T_KEY_VALUE]}";
  }

  private function buildExpressionSubQuery(
    array $expressionSubQuery
  ): string {
    $expressionSubQueryBuild = $this->buildLoop( $expressionSubQuery[ T_KEY_TOKENS ]);
    return "{$expressionSubQuery[T_KEY_METHODS]} ( Select 1 From {$expressionSubQuery[T_KEY_ENTITY]} Where {$expressionSubQueryBuild})";
  }

  private function buildExpressionUnary(
    array $expressionUnary
  ): string {
    [ $expressionField ] = $expressionUnary[ T_KEY_TOKENS ];
    return "{$expressionField[T_KEY_ENTITY]}.{$expressionField[T_KEY_FIELD]}";
  }

  private function buildLoop(
    array $tokens
  ): string {
    foreach( $tokens as $key => $token){
      $tokens[ $key ] = match( $token[ T_KEY_OBJECT ]){
        T_EXPRESSION_LOGICAL => $this->buildExpressionLogical( $token ),
        T_EXPRESSION_COMPARE => $this->buildExpressionCompare( $token ),
        T_EXPRESSION_BETWEEN => $this->buildExpressionBetween( $token ),
        T_EXPRESSION_IN => $this->buildExpressionIn( $token ),
        T_EXPRESSION_NOT_IN => $this->buildExpressionNotIn( $token ),
        T_EXPRESSION_LIKE => $this->buildExpressionLike( $token ),
        T_EXPRESSION_NEGATIVE => $this->buildExpressionNegative( $token ),
        T_EXPRESSION_GROUP => $this->buildExpressionGroup( $token ),
        T_EXPRESSION_UNARY => $this->buildExpressionUnary( $token ),
        T_EXPRESSION_NOT_LIKE => $this->buildExpressionNotLike( $token ),
        T_EXPRESSION_SUBQUERY => $this->buildExpressionSubQuery( $token ),
          default => $token[ T_KEY_OBJECT ]
      };
    }

    return join( " ", $tokens );
  }  

  public function sqlBuild(
  ): string {
    return $this->buildLoop(
      $this->context[ T_KEY_TOKENS ]
    );
  }
}