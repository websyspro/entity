<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use ReflectionFunction;
use function count;

class ExpressionWhere
extends ExpressionUtil
{
  public ReflectionFunction $reflectionFunction;
  public array $context = [];

  public function __construct(
    Closure $closure
  ){
    $this->startupsBuild(
      ClosureUtil::setClosure(
        $closure
      )
    );
  }

  private function createExpressionNode(
    Closure $closure,
    array $scopes,
    array $tokens,
  ): array {
    $tokens = $this->parserTokens( $tokens );
    $tokens = $this->startupsLoop( $closure, $scopes, $tokens );

    return [
      'object' => T_EXPRESSION_NODE,
      'tokens' => $tokens
    ];
  }

  private function createExpressionNegative(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    $tokens = $this->dropNegative( $tokens );
    $tokens = $this->parserTokens( $tokens );
    $tokens = $this->startupsLoop( $closure, $scopes, $tokens );

    return [
      'object' => T_EXPRESSION_NEGATIVE,
      'tokens' => $tokens
    ];
  }  

  private function createExpressionGroup(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    $tokens = $this->dropParenteses( $tokens );
    $tokens = $this->parserTokens( $tokens );
    $tokens = $this->startupsLoop( $closure, $scopes, $tokens );

    return [
      'object' => T_EXPRESSION_GROUP,
      'tokens' => $tokens
    ];
  }
  
  private function createExpressionSubQuery(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    $events = $this->getEventBySubQuery( $tokens );
    $tokens = $this->dropInitialInvalids( $tokens );
    $scopes = $this->getScopes( $closure, $tokens, $scopes );
    $tokens = $this->getContext( $tokens );
    $tokens = $this->dropEndInvalids( $tokens );
    $tokens = $this->parserTokens( $tokens );
    $tokens = $this->startupsLoop( $closure, $scopes, $tokens );

    return [ 
      'object' => T_EXPRESSION_SUBQUERY,
      'events' => $events,
      'tokens' => $tokens
    ];
  }
  
  private function createExpressionLogical(
    array $tokens
  ): array {
    $token = $this->adjustCompare( $tokens );
    
    return [
      'object' => T_EXPRESSION_LOGICAL,
      'tokens' => $token
    ];
  }

  private function createExpressionUnary(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    $tokens = $this->parserTokensCompare( $tokens );
    $tokens = $this->startupsCompareLoop( $closure, $scopes, $tokens );
    $tokens = $this->adjustCompareSimple( $tokens );
    
    return [ 
      'object' => T_EXPRESSION_UNARY,
      'tokens' => $tokens
    ];
  }
  
  private function createExpressionCompare(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    $tokens = $this->parserTokensCompare( $tokens );
    $tokens = $this->startupsCompareLoop( $closure, $scopes, $tokens );
    $tokens = $this->adjustComparePositions( $tokens );
    $tokens = $this->adjustCompareParserValue( $closure, $tokens );
    $tokens = $this->adjustCompareSimple( $tokens );

    return [
      'object' => T_EXPRESSION_COMPARE,
      'tokens' => $tokens
    ];
  }  

  private function startupsLoop(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    for( $i=0; $i < count( $tokens ); $i++ ){
      if( $this->isExpressionNegative( $tokens[$i] )){
        $tokens[$i] = $this->createExpressionNegative($closure, $scopes, $tokens[$i]);
      } else
      if( $this->isExpressionGroup( $tokens[$i] )){
        $tokens[$i] = $this->createExpressionGroup($closure, $scopes, $tokens[$i]);
      } else
      if( $this->isExpressionSubQuery( $tokens[$i] )){
        $tokens[$i] = $this->createExpressionSubQuery($closure, $scopes, $tokens[$i]);
      } else
      if( $this->isExpressionLogical( $tokens[$i] )){
        $tokens[$i] = $this->createExpressionLogical($tokens[$i]);
      } else
      if( $this->isExpressionUnary( $tokens[$i] )){
        $tokens[$i] = $this->createExpressionUnary($closure, $scopes, $tokens[$i]);
      } else
      if( $this->isExpressionCompare( $tokens[$i] )){
        $tokens[$i] = $this->createExpressionCompare($closure, $scopes, $tokens[$i]);
      }
    }

    return $tokens;
  }

  private function createExpressionField(
    array $scopes,
    array $tokens
  ): array {
    [ $variable, $_, $variableName ] = $tokens;
    $entityStructure = ClosureUtil::getEntityStructure(
      $this->getInstance( $variable[ 'value' ], $scopes )
    );

    [ 'value' => $field ] = $variableName;

    return [ 
      'object' => T_EXPRESSION_FIELD,
      'table' => $entityStructure->entity['alias'],
      'field' => $entityStructure->alias[ $field ] ?? $field,
      'type' => $entityStructure->types[ $field ]
    ];
  }

  private function createExpressionEqual(
    array $tokens
  ): array {
    [ $token ] = $tokens;

    return [ 
      'object' => T_EXPRESSION_EQUAL,
      'tokens' => $token
    ];
  }  

  private function createExpressionValue(
    Closure $closure,
    array $tokens
  ): array {
    $isList = $this->isExpressionValueList( $tokens );
    $tokens = $this->dropCurlOpenAndNotDot( $tokens );
    $tokens = $this->updateVariable( $closure, $tokens );
    $tokens = $this->updateEnums( $closure, $tokens );
    $tokens = $this->adjustValues( $tokens );

    return [
      'object' => T_EXPRESSION_VALUE,
      'islist' => $isList,
      'tokens' => $tokens
    ];
  }  
  
  private function startupsCompareLoop(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    for( $i=0; $i < count( $tokens ); $i++ ){
      if( $this->isExpressionField( $tokens[$i] )){
        $tokens[ $i ] = $this->createExpressionField( $scopes, $tokens[$i] );
      } else
      if( $this->isExpressionEquals( $tokens[$i] )){
        $tokens[ $i ] = $this->createExpressionEqual( $tokens[$i] );
      } else
      if( $this->isExpressionValue( $tokens[$i] )){
        $tokens[ $i ] = $this->createExpressionValue( $closure, $tokens[$i] );
      }
    }

    return $tokens;
  }  

  private function startupsBuild(
    Closure $closure
  ): void {
    $tokens = $this->tokensAll( $closure );
    $this->context = $this->createExpressionNode(
      $closure, $this->getScopes( $closure, $tokens ), $this->getContext( $tokens )
    );
  }
}