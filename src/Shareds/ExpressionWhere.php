<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use ReflectionFunction;

class ExpressionWhere
extends ExpressionUtil
{
  public array $expressionNode = [];
  public ReflectionFunction $reflectionFunction;

  public function __construct(
    public Closure $closure
  ){
    $this->startupsBuild();
  }

  private function startupsBuild(
  ): void {
    $this->expressionNode = $this->tokensAll($this->closure);
  }





















  
  














  // private function createExpressionType(
  //   string $type,
  //   array $scopes = [],
  //   array $tokens = [],
  //   array $extras = []
  // ): array {
  //   return array_merge([ 
  //     "type" => $type, "scopes" => $scopes, "tokens" => $tokens ], $extras 
  //   );
  // }

  // private function createExpressionTypeLogical(
  //   array $tokens = []    
  // ): array {
  //   return [ "type" => T_EXPRESSION_LOGICAL, "tokens" => $tokens ];
  // } 
  
  // private function createExpressionTypeNode(
  //   array $scopes = [],
  //   array $expressoinNode = []    
  // ): array {
  //   $createExpressionType = $this->createExpressionType( T_EXPRESSION_NODE, $scopes, $expressoinNode );
  //   return $this->expressionLoop( $scopes, $createExpressionType );
  // }  

  // private function createExpressionTypeUnary(
  //   array $scopes = [],
  //   array $tokes = []    
  // ): array {
  //   $explodeLogicalTokens = $this->explodeLogicalTokens( array_slice( $tokes, 1 ));
  //   $createExpressionType = $this->createExpressionType( T_EXPRESSION_UNARY, $scopes, $explodeLogicalTokens );
  //   return $this->expressionLoop( $scopes, $createExpressionType );
  // }

  // private function createExpressionTypeGroup(
  //   array $scopes = [],
  //   array $tokens = []    
  // ): array {
  //   $explodeLogicalTokens = $this->explodeLogicalTokens( $this->dropUnnecessaryParenteses( $tokens ));
  //   $createExpressionType = $this->createExpressionType( T_EXPRESSION_GROUP, $scopes, $explodeLogicalTokens );
  //   return $this->expressionLoop( $scopes, $createExpressionType );
  // } 
  
  // private function createExpressionTypeSubQuery(
  //   array $scopes = [],
  //   array $tokens = []    
  // ): array {
  //   [ "value" => $query ] = array_slice(
  //     $tokens, $this->find( $tokens, T_FN ) - 2, 1
  //   )[ 0 ];

  //   [ $scopes, $tokens ] = $this->whereScopesAndTokens( 
  //     $scopes, array_slice( $tokens, $this->find( $tokens, T_FN ))
  //   );

  //   $createExpressionType = $this->createExpressionType( T_EXPRESSION_SUBQUERY, $scopes, $tokens, [ "query" => $query ]);
  //   return $this->expressionLoop( $scopes, $createExpressionType );
  // }
  
  // private function createExpressionCompare(
  //   array $scopes = [],
  //   array $tokens = []    
  // ): array {
  //   return [ "type" => T_EXPRESSION_COMPARE, "scopes" => $scopes, "tokens" => $tokens ];
  // }
  
  // private function preparedsTokens(
  //   int $i = 0
  // ): void {
  //   for( $i=0; $i < count( $this->tokens ); $i++ ){
  //     if( is_array( $this->tokens[ $i ])){
  //       $this->tokens[ $i ] = [ 
  //         "number" => $this->tokens[ $i ][0], 
  //         "value" => $this->tokens[ $i ][1], 
  //         "type" => $this->namberToken(
  //           $this->tokens[ $i ][0]
  //         )
  //       ];
  //     } else if( is_string( $this->tokens[ $i ])){
  //       $this->tokens[ $i ] = [ 
  //         "number" => ord( $this->tokens[ $i ] ), 
  //         "value" => $this->tokens[ $i ], 
  //         "type" => $this->namberToken(
  //           ord( $this->tokens[ $i ] )
  //         )
  //       ];
  //     }
  //   }
  // }
  
  // private function startupsPreparedsTokens(
  // ): void {
  //   $this->preparedsTokens();
  // }

  // private function dropUnnecessaryStartTokens(
  // ): void {
  //   for( $i=0; $i < count( $this->tokens ); $i++ ){
  //     if( $this->tokens[ $i ][ "number" ] === T_FN ){
  //       $this->tokens = $this->dropUnnecessaryEndTokens(
  //         array_slice( $this->tokens, $i )
  //       ); break;
  //     }
  //   }
  // }  

  // private function dropUnnecessaryEndTokens(
  //   array $tokens = [],
  //   int $parenteses = 0
  // ): array {
  //   for( $i=0; $i < count( $tokens ); $i++ ){
  //     if( $tokens[ $i ][ "number" ] === T_START_PARENTESES ){
  //       $parenteses++;
  //     }

  //     if( $tokens[ $i ][ "number" ] === T_END_PARENTESES ){
  //       $parenteses--;

  //       if( $parenteses < 0 ){
  //         $tokens = array_slice(
  //           $tokens, 0, $i
  //         ); break;
  //       }          
  //     }

  //     if( $parenteses < 1 ){
  //       if( $tokens[ $i ][ "number" ] === T_SEMICOLON ){
  //         $tokens = array_slice(
  //           $tokens, 0, $i
  //         ); break;
  //       }
  //     }
  //   };

  //   return $tokens;
  // }

  // private function dropWhiteSpacesTokens(
  // ): void {
  //   for( $i=0; $i < count( $this->tokens ); $i++ ){
  //     if( $this->tokens[ $i ][ "number" ] === T_WHITESPACE ){
  //       array_splice( $this->tokens, $i, 1 ); $i--;
  //     } 
  //   }
  // }

  // private function startupsAdjustTokens(
  // ): void {
  //   $this->dropUnnecessaryStartTokens();
  //   $this->dropWhiteSpacesTokens();
  // }

  // private function defineScope(
  //   array $scopes = [],   
  //   array $tokens = []
  // ): array {
  //   [ $instance, $variable ] = $tokens;
  //   return array_merge( 
  //     $scopes, [[
  //       "instance" => $instance[ "value" ],
  //       "variable" => $variable[ "value" ]
  //     ]]
  //   );
  // }

  // private function dropUnnecessaryParenteses(
  //   array $tokens
  // ): array {
  //   for( $i=0; $i < count( $tokens ); $i++ ){
  //     if( $tokens[ $i ][ "number" ] === T_START_PARENTESES ){
  //       if( $tokens[ count( $tokens ) - 1][ "number" ] === T_END_PARENTESES ){
  //         array_splice( $tokens, $i, 1 );
  //         array_splice( $tokens, count( $tokens ) - 1, 1 ); $i--;
  //       }
  //     }

  //     break;
  //   }

  //   return $tokens;
  // }

  // private function isLogical(
  //   int $number
  // ): bool {
  //   return $number === T_LOGICAL_AND
  //       || $number === T_LOGICAL_OR
  //       || $number === T_BOOLEAN_AND
  //       || $number === T_BOOLEAN_OR;
  // }  

  // private function explodeLogicalTokens(
  //   array $expressionNode = [],
  //   array $tokensCurrent = [],
  //   array $tokensAccumulate = [],
  //     int $tokensDepth = 0
  // ): array {
  //   for( $i=0; $i < count( $expressionNode ); $i++ ){
  //     if( $this->isLogical( $expressionNode[ $i ][ "number" ]) && $tokensDepth === 0 ){
  //       if( $tokensCurrent ){
  //         $tokensAccumulate[] = $tokensCurrent;
  //         $tokensCurrent = [];
  //       }

  //       $tokensAccumulate[] = $this->createExpressionTypeLogical(
  //         $expressionNode[ $i ]
  //       );

  //       continue;
  //     }

  //     $tokensCurrent[] = $expressionNode[ $i ];

  //     if( $expressionNode[ $i ][ "number" ] === T_START_PARENTESES ) $tokensDepth++;
  //     if( $expressionNode[ $i ][ "number" ] === T_END_PARENTESES ) $tokensDepth--;
  //   }

  //   if( $tokensCurrent ){
  //     $tokensAccumulate[] = $tokensCurrent;
  //   }

  //   return $tokensAccumulate;
  // }

  // private function whereScopesAndTokens(
  //   array $scopes = [],
  //   array $tokens = []
  // ): array {
  //   $defineScope = $this->defineScope(
  //     $scopes, array_slice( $tokens, 
  //       $this->find( $tokens, T_START_PARENTESES ) + 1, 
  //       $this->find( $tokens, T_END_PARENTESES ) - 2
  //     )
  //   );

  //   return [ $defineScope, $this->explodeLogicalTokens( 
  //     array_slice( $tokens, $this->find( $tokens, T_DOUBLE_ARROW ) + 1 )
  //   )];
  // }

  // private function isExpressionUnary(
  //   array $expressoinNode = []
  // ): bool {
  //   if( isset( $expressoinNode[ 0 ])){
  //     if( $expressoinNode[ 0 ][ "number" ] === T_NOT ){
  //       return true;
  //     }
  //   }
    
  //   return false;
  // }

  // private function isExpressionGroup(
  //   array $expressoinNode = []
  // ): bool {
  //   if( isset( $expressoinNode[ 0 ])){
  //     if( $expressoinNode[ 0 ][ "number" ] === T_START_PARENTESES ){
  //       if( $expressoinNode[ count( $expressoinNode ) - 1 ][ "number" ] === T_END_PARENTESES ){
  //         return true;
  //       }
  //     }
  //   }
    
  //   return false;
  // }

  // private function isExpressionSubQuery(
  //   array $expressoinNode = []
  // ): bool {
  //   if( isset( $expressoinNode[ 0 ])){
  //     if( $this->find( $expressoinNode, T_FN ) !== -1 ){
  //       return true;
  //     }
  //   }
    
  //   return false;
  // }

  // public function isExpressionCompare(
  //   array $tokens
  // ): bool {
  //   return $this->find( $tokens, T_EQUAL ) !== -1
  //       || $this->find( $tokens, T_IS_EQUAL ) !== -1
  //       || $this->find( $tokens, T_IS_IDENTICAL ) !== -1
  //       || $this->find( $tokens, T_IS_NOT_EQUAL ) !== -1
  //       || $this->find( $tokens, T_IS_NOT_IDENTICAL ) !== -1
  //       || $this->find( $tokens, T_IS_GREATER_OR_EQUAL ) !== -1
  //       || $this->find( $tokens, T_IS_SMALLER_OR_EQUAL ) !== -1;
  // }

  // private function expressionLoop(
  //   array $scopes = [],
  //   array $expressionNode = []    
  // ): array {
  //   for( $i = 0; $i < count( $expressionNode[ "tokens" ] ); $i++ ){
  //     if( $this->isExpressionUnary( $expressionNode[ "tokens" ][ $i ])){
  //       $expressionNode[ "tokens" ][ $i ] = $this->createExpressionTypeUnary( 
  //         $scopes, $expressionNode[ "tokens" ][ $i ]
  //       );
  //     } else 
  //     if( $this->isExpressionGroup( $expressionNode["tokens"][ $i ])){
  //       $expressionNode ["tokens" ][ $i ] = $this->createExpressionTypeGroup( 
  //         $scopes, $expressionNode[ "tokens" ][ $i ]
  //       );
  //     } else 
  //     if( $this->isExpressionSubQuery( $expressionNode[ "tokens" ][ $i ])){
  //       $expressionNode[ "tokens" ][ $i ] = $this->createExpressionTypeSubQuery( 
  //         $scopes, $expressionNode[ "tokens" ][ $i ]
  //       );
  //     } else 
  //     if( $this->isExpressionCompare( $expressionNode[ "tokens" ][ $i ])){
  //       $expressionNode[ "tokens" ][ $i ] = $this->createExpressionCompare(
  //         $scopes, $expressionNode[ "tokens" ][ $i ]
  //       );
  //     }
  //   }

  //   return $expressionNode;
  // }

  // private function createScopes(
  //   array $scopes = [],
  //   array $tokens = [] 
  // ): array {
  //   return array_merge( $scopes, $tokens );
  // }

  // private function createClosure(
  // ): array {
  //   return [ null, null ];
  // }

  // private function startupsWhereTokens(
  // ): void {
  //   [ $scopes, $tokens ] = $this->whereScopesAndTokens( [], $this->tokens );
  //   $this->tokens = $this->createExpressionTypeNode( $scopes, $tokens );
  // }
}