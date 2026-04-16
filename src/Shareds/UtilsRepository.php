<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionFunction;

class UtilsRepository
{
  public function __construct(){}

  public function readScriptUses(
  ): Collection {
    return new Collection();
  }
  
  public function readRowsFromScript(
    ReflectionFunction $fn
  ): Collection {
    return new Collection(
      file( $fn->getFileName())
    );
  }

  public function dropBreaksLines(
    string $scriptFull
  ): string {
    return Util::replace(
      [ "#\r#", "#\n\s*#" ], trim(
        $scriptFull
      )
    );
  }

  public function dropParenthesesExtra(
    string $scriptFull,
    string $scriptFullNew = "",
    int $parenthesesCount = 0
  ): string {
    for( $i = 0; $i < Util::sizeText( $scriptFull ); $i++){
      if( $scriptFull[ $i ] === ")" ){
        $parenthesesCount--;
        if( $parenthesesCount === 0 ){
          break;
        }
      }

      if( $parenthesesCount >= 1 ){
        $scriptFullNew .= $scriptFull[ $i ];
      }

      if( $scriptFull[ $i ] === "(" ){
        $parenthesesCount++;
      }
    }

    return trim( $scriptFullNew );
  } 
  
  public function normalizedScriptInclude(
    AbstractRepository $abstractRepository,
    string $scriptFull,
    string $type = "include"
  ): Collection {
    $scriptFull = $this->dropBreaksLines( $scriptFull );
    $scriptFull = Util::match( "#^(->)?{$type}#", $scriptFull ) === false
      ? $this->dropParenthesesExtra( 
          Util::match("#^\\(#", $scriptFull ) === false 
            ? "({$scriptFull}" : "{$scriptFull}"
        )
      : $this->dropParenthesesExtra(
          Util::replace( "#^(->)?{$type}#", $scriptFull )
        );

    $scriptFull = new Collection( explode( "->{$type}(", $scriptFull )); 
    $scriptFull = $scriptFull->mapper(
      fn( string $script ) => new IncludeItem(
        $abstractRepository, Util::replace( 
          [ "#^\\(#", "#\\)*$#", "#(^\s*)|(\s*$)#" ], $script 
        )
      )
    );

    return $scriptFull;
  }
}