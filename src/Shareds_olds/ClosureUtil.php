<?php

namespace Websyspro\Entity\Shareds;

use ReflectionFunction;
use Closure;

class ClosureUtil
{
  public static array $cacheReflectionFunction;
  public static array $cacheClosureScript;

  public static function getReflectFunction(
    Closure $closure
  ): ReflectionFunction {
    if( isset( ClosureUtil::$cacheReflectionFunction ) === false ){
      ClosureUtil::$cacheReflectionFunction = [];
    }

    if( isset( ClosureUtil::$cacheReflectionFunction[ spl_object_id( $closure )])){
      return ClosureUtil::$cacheReflectionFunction[ spl_object_id( $closure )];
    }

    ClosureUtil::$cacheReflectionFunction[ spl_object_id( $closure )] = new ReflectionFunction( $closure );
    return ClosureUtil::$cacheReflectionFunction[ spl_object_id( $closure )];
  }

  public static function getTokensFromClosure(
    Closure $closure
  ): array {
    if( isset( ClosureUtil::$cacheClosureScript ) === false ){
      ClosureUtil::$cacheClosureScript = [];
    }

    if( isset( ClosureUtil::$cacheClosureScript[ spl_object_id( $closure )])){
      return ClosureUtil::$cacheClosureScript[ spl_object_id( $closure )];
    }

    ClosureUtil::$cacheClosureScript[ spl_object_id( $closure )] = ExpressionUtil::getTokensFromClosure($closure);
    return ClosureUtil::$cacheClosureScript[ spl_object_id( $closure )];
  }  
}