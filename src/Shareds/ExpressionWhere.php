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
    $tokens = $this->loopTokens( $tokens, $scopes, $closure );
    $tokens = $this->joinsTokens( $tokens );
    $tokens = $this->revaliderTokens( $tokens );

    return [
      T_KEY_OBJECT => T_EXPRESSION_NODE,
      T_KEY_TOKENS => $tokens
    ];
  }

  private function createExpressionNegative(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    $tokens = $this->dropNegative( $tokens );
    $tokens = $this->parserTokens( $tokens );
    $tokens = $this->loopTokens( $tokens, $scopes, $closure );

    return [
      T_KEY_OBJECT => T_EXPRESSION_NEGATIVE,
      T_KEY_TOKENS => $tokens
    ];
  }  

  private function createExpressionGroup(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    $tokens = $this->dropParenteses( $tokens );
    $tokens = $this->parserTokens( $tokens );
    $tokens = $this->loopTokens( $tokens, $scopes, $closure );
    $tokens = $this->joinsTokens( $tokens );
    $tokens = $this->revaliderTokens( $tokens );

    return [
      T_KEY_OBJECT => T_EXPRESSION_GROUP,
      T_KEY_TOKENS => $tokens
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
    $tokens = $this->loopTokens( $tokens, $scopes, $closure );
    $tokens = $this->joinsTokens( $tokens );
    $tokens = $this->revaliderTokens( $tokens );

    return [ 
      T_KEY_OBJECT => T_EXPRESSION_SUBQUERY,
      T_KEY_METHOD => $events,
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
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    $tokens = $this->parserTokensCompare( $tokens );
    $tokens = $this->loopCompareTokens( $closure, $scopes, $tokens );
    
    return [ 
      T_KEY_OBJECT => T_EXPRESSION_UNARY,
      T_KEY_TOKENS => $tokens
    ];
  }
  
  private function createExpressionCompare(
    Closure $closure,
    array $scopes,
    array $tokens
  ): array {
    $tokens = $this->parserTokensCompare( $tokens );
    $tokens = $this->loopCompareTokens( $closure, $scopes, $tokens );
    $tokens = $this->adjustComparePositions( $tokens );
    $tokens = $this->adjustCompareParserValue( $closure, $tokens );

    return [
      T_KEY_OBJECT => T_EXPRESSION_COMPARE,
      T_KEY_TOKENS => $tokens
    ];
  }  

  private function loopTokens(
    array $tokens,
    array $scopes,
    Closure $closure,
  ): array {
    foreach($tokens as $key => $token){
      $tokens[$key] = match($this->getExpressionType($token)){
        T_IS_EXPRESSION_NEGATIVE => $this->createExpressionNegative($closure, $scopes, $token),
        T_IS_EXPRESSION_GROUP => $this->createExpressionGroup($closure, $scopes, $token ),
        T_IS_EXPRESSION_SUBQUERY => $this->createExpressionSubQuery($closure, $scopes, $token),  
        T_IS_EXPRESSION_LOGICAL => $this->createExpressionLogical( $token ),
        T_IS_EXPRESSION_UNARY => $this->createExpressionUnary($closure, $scopes, $token),
        T_IS_EXPRESSION_COMPARE => $this->createExpressionCompare($closure, $scopes, $token)
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
      T_KEY_TABLE => $table,
      T_KEY_FIELD => $field,
      T_KEY_TYPE => $type,
      T_KEY_METHOD => $methods
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
    $tokens = $this->updateTokensVariable( $tokens, $closure );
    $tokens = $this->updateTokensEnums( $tokens, $closure );
    $tokens = $this->adjustValues( $tokens );

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
        T_IS_EXPRESSION_FIELD => $this->createExpressionField($token, $scopes, $closure),
        T_IS_EXPRESSION_EQUAL => $this->createExpressionEqual($token),
        T_IS_EXPRESSION_VALUE => $this->createExpressionValue($closure, $token)
      };
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