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
      // 'closure' => $closure,
      // 'scopes' => $scopes,
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
      // 'closure' => $closure,
      // 'scopes' => $scopes,
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
      // 'closure' => $closure,
      // 'scopes' => $scopes,
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
      // 'events' => $events,
      // 'closure' => $closure,
      // 'scopes' => $scopes,
      'tokens' => $tokens
    ];
  }
  
  private function createExpressionLogical(
    array $tokens
  ): array {
    [ $token ] = $tokens;
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

    return [ 
      'object' => T_EXPRESSION_UNARY,
      // 'closure' => $closure,
      // 'scopes' => $scopes,
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

    return [ 
      'object' => T_EXPRESSION_COMPARE,
      // 'closure' => $closure,
      // 'scopes' => $scopes,
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
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    [ $variable, $_, $variableName ] = $tokens;
    $entity = $this->getInstance( $variable[ 'value' ], $scopes );

    return [ 
      'object' => T_EXPRESSION_FIELD,
      // 'closure' => $closure,
      // 'scopes' => $scopes,
      'tokens' => [
        'entity' => $entity,
        'field' => $variableName[ 'value' ]
      ]
    ];
  }

  private function createExpressionEqual(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    [ $token ] = $tokens;
    return [ 
      'object' => T_EXPRESSION_EQUAL,
      // 'closure' => $closure,
      // 'scopes' => $scopes,
      'tokens' => $token
    ];
  }  

  private function createExpressionValue(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    return [ 
      'object' => T_EXPRESSION_VALUE,
      // 'closure' => $closure,
      // 'scopes' => $scopes,
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
        $tokens[ $i ] = $this->createExpressionField( $closure, $scopes, $tokens[$i]);
      } else
      if( $this->isExpressionEquals( $tokens[$i] )){
        $tokens[ $i ] = $this->createExpressionEqual( $closure, $scopes, $tokens[$i]);
      } else
      if( $this->isExpressionValue( $tokens[$i] )){
        $tokens[ $i ] = $this->createExpressionValue( $closure, $scopes, $tokens[$i]);
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