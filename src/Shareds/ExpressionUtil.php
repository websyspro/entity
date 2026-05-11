<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;

class ExpressionUtil
{
  public static function getTokenAll(
    string $script
  ): array {
    return token_get_all( "<?php {$script}" );
  }

  public static function createTokens(
    Collection $tokens
  ): Collection {
    return $tokens->slice(1)->mapper(
      fn( array|string $token ) => new Token( $token )
    );
  }

  public static function dropWhiteSpace(
    Collection $tokens
  ): Collection {
    return $tokens->where(
      fn( Token $token ) => $token->isWhiteSpace() === false
    );
  }

  public static function dropUnnecessaryStartScript(
    Collection $tokens,
    int $index = 0
  ): Collection {
    $tokens->spliceOut( 0, ExpressionUtil::find( $tokens, T_FN ));
    return $tokens;
  }

  public static function dropUnnecessaryEndScripts(
    Collection $tokens,
    int $parenteses = 0,
    int $index = 0
  ): Collection {
    for( $index = 0; $index < $tokens->count(); $index++ ){
      $token = $tokens->getOneOrFail( $index );

      if( $token instanceof Token ){
        if( $token->id !== Token::T_UNKNOWN ){
          continue;
        }

        if( $token->value === Token::T_PARENTHESES_OPEN ){
          $parenteses++;
        }

        if( $token->value === Token::T_PARENTHESES_CLOSE ){
          $parenteses--;

          if( $parenteses < 0 ){
            $tokens->spliceOut( $index );
            break;
          }          
        }

        if( $parenteses < 1 ){
          if( $token->value === Token::T_SEMICOLON ){
            $tokens->spliceOut( $index );
            break;
          }
        }
      }
    };

    return $tokens;
  }

  public static function find(
    Collection $tokens,
    int|string $find
  ): int {
    return Util::isString( $find ) === false
      ? $tokens->indexOf( fn( Token $token ) => $token->id === $find )
      : $tokens->indexOf( fn( Token $token ) => $token->value === $find );
  }

  public static function extractGroup(
    Collection $tokens
  ): Collection {
    return $tokens->slice(1, -1);
  }

  public static function isLogical(
    Token $token
  ): bool {
    return Util::inArray( $token->id, [
      T_BOOLEAN_AND, T_LOGICAL_OR,
      T_LOGICAL_AND, T_BOOLEAN_OR
    ]);
  }

  public static function isExpressionGroup(
    Collection $tokens
  ): bool {
    if( $tokens->exist() === false ){
      return false;
    }

    [ $token ] = $tokens->toArray();
    return $token->value === Token::T_PARENTHESES_OPEN;
  }

  public static function isExpressionSubQuery(
    Collection $tokens
  ): bool {
    if( $tokens->exist() === false ){
      return false;
    }

    return ExpressionUtil::find( $tokens, T_FN ) !== -1
        && ExpressionUtil::find( $tokens, T_DOUBLE_ARROW ) !== -1;
  }  

  public static function createExpressionGroup(
    Collection $tokens,
    Collection $parameters
  ): Collection {
    return Collection::create([
      new ExpressionGroup( $tokens, $parameters )
    ]);
  }

  public static function createSplitLogical(
    Collection $tokens,
    array $tokensCurrent = [],
    array $tokensAccumulate = [],
    int $depth = 0
  ): Collection {
    foreach( $tokens->toArray() as $token ){
      if( ExpressionUtil::isLogical( $token ) && $depth === 0 ){
        if( $tokensCurrent ){
          $tokensAccumulate[] = $tokensCurrent;
          $tokensCurrent = [];
        }

        $tokensAccumulate[] = $token;
        continue;
      }

      $tokensCurrent[] = $token;

      if( $token->value === Token::T_PARENTHESES_OPEN ) $depth++;
      if( $token->value === Token::T_PARENTHESES_CLOSE ) $depth--;
    }

    if( $tokensCurrent ){
      $tokensAccumulate[] = $tokensCurrent;
    }

    return Collection::create( $tokensAccumulate );
  }

  public static function ExpressionTypesValid(
    array|Token $tokens,
    Collection $scopes
  ): array|Token|ExpressionLogical|ExpressionGroup|ExpressionSubQuery|ExpressionCompare {
    if( $tokens instanceof Token ){
      return new ExpressionLogical( $tokens );
    } else
    if( ExpressionUtil::isExpressionGroup( Collection::create( $tokens ))){
      return new ExpressionGroup( Collection::create( $tokens ), $scopes);
    } else 
    if( ExpressionUtil::isExpressionSubQuery( Collection::create( $tokens ))){
      return new ExpressionSubQuery( Collection::create( $tokens ), $scopes);
    } else return new ExpressionCompare( Collection::create( $tokens ), $scopes );
  }

  public static function ExpressionTypes(
    Collection $tokensLogical,
    Collection $parameters
  ): Collection {
    return $tokensLogical->mapper(
      fn(array|Token $tokens) => (
        ExpressionUtil::ExpressionTypesValid( $tokens, $parameters )
      )
    );
  }
  
  public static function expressionStructureValid(
    Collection $tokens,
    Collection $scopes
  ): Collection {
    return ExpressionUtil::isExpressionGroup( $tokens )
      ? ExpressionUtil::createExpressionGroup( $tokens, $scopes )
      : ExpressionUtil::ExpressionTypes(
        ExpressionUtil::createSplitLogical( $tokens ), $scopes
      );
  }  
}