<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use function 
  count, 
  array_slice;

class ExpressionUtil_____
{
  public static function where(
    array $items,
    Closure $closure
  ): array {
    return array_values( array_filter( $items, $closure ));
  }

  public static function mapper(
    array $items,
    Closure $closure
  ): array {
    return array_map( $closure, $items );
  }  

  public static function slice(
    array $items,
    int $offSet, 
    int|null $length = null 
  ): array {
    return array_slice( $items, $offSet, $length );
  }

  public static function findByToken(
    array $tokens,
    int $tokemId,
    int $i = -1
  ): int {
    while( $i < count( $tokens )){
      if( isset( $tokens[ $i ] )){
        if( $tokens[ $i ] instanceof Token ){
          if( $tokens[ $i ]->id === $tokemId ){
            break;
          }
        }
      }

      $i++;
    }


    return $i;
  }

  public static function joinWithSpace(
    array $items
  ): string {
    return implode( " ", $items );
  }  

  public static function extractScript(
    Closure $closure
  ): string {
    return ExpressionUtil::joinWithSpace(
      ExpressionUtil::where(
        ExpressionUtil::slice( file( ClosureUtil::getReflectFunction( $closure )->getFileName()),
          ClosureUtil::getReflectFunction( $closure )->getStartLine() - 1,
          ClosureUtil::getReflectFunction( $closure )->getEndLine() - ClosureUtil::getReflectFunction( $closure )->getStartLine() + 1
        ), fn( string $closureLine ) => !str_starts_with( trim( $closureLine ), "//" )
      )
    );
  }

  public static function tokenAll(
    Closure $closure
  ): array {
    return ExpressionUtil::slice(
      token_get_all( sprintf( 
        "<?php %s", ExpressionUtil::extractScript( $closure )
      )), 1
    );
  }

  public static function getTokensFromClosure(
    Closure $closure
  ): array {
    $tokensAll =  ExpressionUtil::where(
      ExpressionUtil::mapper(
        ExpressionUtil::tokenAll( $closure ), 
          fn( array|string $token ) => new Token( $token )
        ), 
      fn( Token $token ) => $token->id !== T_WHITESPACE
    );

    return ExpressionUtil::dropUnnecessaryEndScripts( ExpressionUtil::slice(
      $tokensAll, ExpressionUtil::findByToken( $tokensAll, T_FN )
    ));
  }  

  public static function dropUnnecessaryEndScripts(
    array $tokens,
    int $parenteses = 0
  ): array {
    foreach( $tokens as $index => $token ){
      if( $token instanceof Token ){
        if( $token->id === T_START_PARENTESES ){
          $parenteses++;
        }

        if( $token->id === T_END_PARENTESES ){
          $parenteses--;

          if( $parenteses < 0 ){
            return ExpressionUtil::slice( $tokens, 0, $index );
          }          
        }

        if( $parenteses < 1 ){
          if( $token->id === T_SEMICOLON ){
            return ExpressionUtil::slice( $tokens, 0, $index );
          }
        }
      }
    };

    return $tokens;
  }

  private static function readIsClousere(
    array $tokens
  ): bool {
    return ExpressionUtil::findByToken($tokens, T_FN) !== -1; 
  }

  private static function readIsLogical(
    Token $token
  ): bool {
    return $token->id === T_LOGICAL_AND
        || $token->id === T_LOGICAL_OR
        || $token->id === T_BOOLEAN_AND
        || $token->id === T_BOOLEAN_OR;
  }  

  public static function dropParentesesInitiais(
    array $tokens,
    int $i = 0   
  ): array {
    if( empty( $tokens )){
      return [];
    }
    
    while( $i < count( $tokens )){
      if( $tokens[ $i ]->id === T_START_PARENTESES ){
        $tokens = ExpressionUtil::slice( $tokens, 1, -1 ); $i++;
      } else break;
    }

    return $tokens;
  }

  public static function explodeTokensByLogical(
    array $tokens,
    array $tokensCurrent = [],
    array $tokensAccumulate = [],
    int $depth = 0    
  ): array {
    foreach( $tokens as $token ){
      if( $token instanceof Token ){
        if( ExpressionUtil::readIsLogical( $token ) && $depth === 0 ){
          if( $tokensCurrent ){
            $tokensAccumulate[] = $tokensCurrent;
            $tokensCurrent = [];
          }

          $tokensAccumulate[] = $token;
          continue;
        }

        $tokensCurrent[] = $tokens;

        if( $token->id === T_START_PARENTESES ) $depth++;
        if( $token->id === T_END_PARENTESES ) $depth--;
      }
    }

    if( $tokensCurrent ){
      $tokensAccumulate[] = $tokensCurrent;
    }

    return $tokensAccumulate;
  }

  public static function getCursorEgual(
    array $tokens
  ): int {
    foreach( $tokens as $key => $token ){
      if( $token instanceof Token ){
        if( $token->id === T_EQUAL ) return $key;
        if( $token->id === T_IS_EQUAL ) return $key;
        if( $token->id === T_IS_IDENTICAL ) return $key;
        if( $token->id === T_IS_NOT_EQUAL ) return $key;
        if( $token->id === T_IS_NOT_IDENTICAL ) return $key;
        if( $token->id === T_IS_GREATER_OR_EQUAL ) return $key;
        if( $token->id === T_IS_GREATER_OR_EQUAL ) return $key;
      }
    }

    return -1;
  }

    public static function getScopeFromTokens(
    array $tokens,
    Closure $closure
  ): array {
    return ExpressionUtil::mapper(
      array_chunk(
        ExpressionUtil::where(
        ExpressionUtil::slice( $tokens, 
          ExpressionUtil::findByToken( $tokens, T_START_PARENTESES ) + 1,
          ExpressionUtil::findByToken( $tokens, T_END_PARENTESES ) - 2, 
        ), fn( Token $token ) => $token->id !== T_COMMA ), 2
      ), function( array $scopeArr ) use( $closure ) {
        [ $variableType, $variable ] = $scopeArr;
        return new Scope( $variable->value, $variableType->value, $closure );
      }
    );
  }
  
  public static function getBodyFromTokens(
    array $tokens
  ): array {
    return ExpressionUtil::explodeTokensByLogical(
        ExpressionUtil::dropParentesesInitiais(
          ExpressionUtil::slice( $tokens, ExpressionUtil::findByToken( $tokens, T_DOUBLE_ARROW ) + 1
        )
       )
    );
  }

  public static function readIsField(
    array $tokens
  ): bool {
    foreach( $tokens as $key => $token ){
      if( $token instanceof Token ){
        if( isset( $tokens[ $key + 1 ]) && isset($tokens[ $key + 2 ])){
          $tokenObjectOperator = $tokens[ $key + 1 ];
          $tokenString = $tokens[ $key + 2 ];

          if( $tokenObjectOperator instanceof Token && $tokenString instanceof Token ){
            if( $tokenObjectOperator->id === T_OBJECT_OPERATOR && $tokenString->id === T_STRING ){
              return true;
            }
          }
        }
      }
    }

    return false;
  }
  
  public static function readIsEguals(
    array $tokens
  ): bool {
    return ExpressionUtil::findByToken( $tokens, T_EQUAL ) !== -1
        || ExpressionUtil::findByToken( $tokens, T_IS_EQUAL ) !== -1
        || ExpressionUtil::findByToken( $tokens, T_IS_IDENTICAL ) !== -1
        || ExpressionUtil::findByToken( $tokens, T_IS_NOT_EQUAL ) !== -1
        || ExpressionUtil::findByToken( $tokens, T_IS_NOT_IDENTICAL ) !== -1
        || ExpressionUtil::findByToken( $tokens, T_IS_GREATER_OR_EQUAL ) !== -1
        || ExpressionUtil::findByToken( $tokens, T_IS_SMALLER_OR_EQUAL ) !== -1;
  }  

  public static function readIsExpressionGroup(
    array $tokens
  ): bool {
    [ $tokenFirst, $tokenLast ] = array_merge( 
      ExpressionUtil::slice( $tokens,  0, 1 ), 
      ExpressionUtil::slice( $tokens, -1, 1 )
    );

    if( $tokenFirst instanceof Token && $tokenLast instanceof Token ){
      return $tokenFirst->id === T_START_PARENTESES 
          && $tokenLast->id === T_END_PARENTESES;
    }

    return false;
  }

  public static function readIsExpressionNegative(
    array $tokens
  ): bool {
    [ $tokenFirst ] = ExpressionUtil::slice( $tokens,  0, 1 );

    if( $tokenFirst instanceof Token ){
      return $tokenFirst->id === T_NOT;
    }

    return false;
  }
  
  public static function readIsExpressionCompareValue(
    array $tokens
  ): bool {
    if( ExpressionUtil::readIsClousere( $tokens )){
      return false;
    }

    return ExpressionUtil::readIsField( ExpressionUtil::slice( $tokens, 0, ExpressionUtil::getCursorEgual( $tokens )))
        && ExpressionUtil::readIsField( ExpressionUtil::slice( $tokens, ExpressionUtil::getCursorEgual( $tokens )))
        && ExpressionUtil::readIsEguals( $tokens ) === true;
  }  

  public static function createExpressionGroup____(
    array $scopes,
    Token|array $tokens
  ): array {
    return [ new ExpressionGroup( $scopes, $tokens )];
  }

  public static function readExpression(
    array $scopes,
    mixed $tokens
  ): mixed {
    if( $tokens instanceof Token ){
      return new ExpressionLogical( $tokens );
    } else
    if( ExpressionUtil::readIsExpressionNegative( $tokens )){
      return new ExpressionNegative( $scopes, $tokens );
    } else
    if( ExpressionUtil::readIsExpressionGroup( $tokens )){
      return new ExpressionGroup( $scopes, $tokens );
    } else
    if( ExpressionUtil::readIsExpressionCompareValue( $tokens ) ){
      return new ExpressionCompareValue( $scopes, $tokens ); 
    }

    return [ "...." ];
  }  
  
  public static function readExpressionsLoop(
    array $scopes,
    array $tokensAll
  ): array {
    return ExpressionUtil::mapper(
      $tokensAll, fn( Token|array $tokens ) => ExpressionUtil::readExpression( $scopes, $tokens )
    );
  }

  public static function readTokensFromNodes(
    array $scopes,
    array $tokens
  ): mixed {
    return ExpressionUtil::readIsExpressionGroup( $tokens )
      ? ExpressionUtil::createExpressionGroup( $scopes, $tokens )
      : ExpressionUtil::readExpressionsLoop( $scopes, ExpressionUtil::explodeTokensByLogical( $tokens ));
  }
}