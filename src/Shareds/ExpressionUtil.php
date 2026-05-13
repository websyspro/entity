<?php

namespace Websyspro\Entity\Shareds;

use Closure;
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
    int|string|array $find
  ): int {
    if( Util::isArray( $find )){
      return $tokens->indexOf( fn( Token $token ) => Util::inArray(
        $token->id, $find
      ));
    }

    return Util::isString( $find ) === false
      ? $tokens->indexOf( fn( Token $token ) => $token->id === $find )
      : $tokens->indexOf( fn( Token $token ) => $token->value === $find );
  }

  public static function findCompare(
    Collection $tokens
  ): int {
    return $tokens->indexOf( fn( Token $token ) => (
      ExpressionUtil::isCompared( $token )
    ));
  }  

  public static function extractGroup(
    Collection $tokens
  ): Collection {
    return $tokens->slice( 1, -1 );
  }

  public static function isField(
    Collection $tokens
  ): bool {
    return ExpressionUtil::find( $tokens, T_VARIABLE ) !== -1
        && ExpressionUtil::find( $tokens, T_OBJECT_OPERATOR ) !== -1
        && ExpressionUtil::find( $tokens, T_STRING ) !== -1;
  }  

  public static function isCompared(
    Token $token
  ): bool {
    return Util::inArray(
      $token->id, Token::T_COMPARE_LIST
    );
  }

  public static function isExistsFN(
    Collection $tokens
  ): bool {
    if( $tokens->exist() === false ){
      return false;
    }

    $token = $tokens->getOneOrFail(0);
    if( $token instanceof Token ){
      return $token->id === T_FN;
    }

    return false; 
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
    Collection $scopes,
    Closure $closure
  ): Collection {
    return Collection::create([
      new ExpressionGroup( $tokens, $scopes, $closure )
    ]);
  }

  public static function spliteLogical(
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
    Collection $scopes,
    Closure $closure
  ): array|Token|ExpressionLogical|ExpressionGroup|ExpressionSubQuery|ExpressionCompare {
    if( $tokens instanceof Token ){
      return new ExpressionLogical( $tokens );
    } else
    if( ExpressionUtil::isExpressionGroup( Collection::create( $tokens ))){
      return new ExpressionGroup( Collection::create( $tokens ), $scopes, $closure );
    } else 
    if( ExpressionUtil::isExpressionSubQuery( Collection::create( $tokens ))){
      return new ExpressionSubQuery( Collection::create( $tokens ), $scopes, $closure );
    } else return new ExpressionCompare( Collection::create( $tokens ), $scopes, $closure );
  }

  public static function expressionTypes(
    Collection $tokensLogical,
    Collection $parameters,
    Closure $closure
  ): Collection {
    return $tokensLogical->mapper(
      fn(array|Token $tokens) => (
        ExpressionUtil::ExpressionTypesValid( $tokens, $parameters, $closure )
      )
    );
  }
  
  public static function expressionTokenType(
    Collection $tokens,
    Collection $scopes,
    Closure $closure
  ): Collection {
    return ExpressionUtil::isExpressionGroup( $tokens )
      ? ExpressionUtil::createExpressionGroup( $tokens, $scopes, $closure )
      : ExpressionUtil::expressionTypes(
        ExpressionUtil::spliteLogical( $tokens ), $scopes, $closure
      );
  }  
}