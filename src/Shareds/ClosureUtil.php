<?php

namespace Websyspro\Entity\Shareds;


use Websyspro\Entity\Enums\MetaType;
use ReflectionFunction;
use Closure;

use function array_slice, sprintf, count;

class ClosureUtil
{
  public static array $cacheReflectionFunction;
  public static array $cacheEntityStructure;
  public static array $cacheClosures;
  public static array $cacheUses;
  public static array $cacheParams;

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

  public static function getEntityStructure(
    string $entity
  ): EntityStructure {
    if( isset( ClosureUtil::$cacheEntityStructure ) === false ){
      ClosureUtil::$cacheEntityStructure = [];
    }

    if( class_exists( $entity )){
      if( isset( ClosureUtil::$cacheEntityStructure[ $entity ])){
        if( ClosureUtil::$cacheEntityStructure[ $entity ] instanceof EntityStructure ){
          return ClosureUtil::$cacheEntityStructure[ $entity ];
        }        
      }
    }
    
    $entityStructure = $entity::meta( MetaType::Query );
    ClosureUtil::$cacheEntityStructure[ $entity ] = $entityStructure;
    return $entityStructure;
  }  

  public static function setClosure(
    Closure $closure
  ): Closure {
    if( isset( ClosureUtil::$cacheClosures ) === false ){
      ClosureUtil::$cacheClosures = [];
    }

    if( isset( ClosureUtil::$cacheClosures[ spl_object_id( $closure )])){
      return ClosureUtil::$cacheClosures[ spl_object_id( $closure )];
    }

    ClosureUtil::$cacheClosures[ spl_object_id( $closure )] = $closure;
    return ClosureUtil::$cacheClosures[ spl_object_id( $closure )];
  }  
  
  public static function getClosure(
    Closure $closure
  ): Closure {
    return ClosureUtil::setClosure( $closure );
  }

  public static function getRowsFromClosure(
    ReflectionFunction $cacheReflectionFunction  
  ): array {
    $getRowsFromClosure = file( $cacheReflectionFunction->getFileName());
    return array_values( array_filter( $getRowsFromClosure, fn( string $row ) => !str_starts_with( trim( $row ), "//" )));
  }  

  public static function getUses(
    Closure $closure  
  ): array {
    if( isset( ClosureUtil::$cacheUses ) === false ){
      ClosureUtil::$cacheUses = [];
    }

    if( isset( ClosureUtil::$cacheUses[ spl_object_id( $closure )])){
      return ClosureUtil::$cacheUses[ spl_object_id( $closure )];
    }

    $getRowsFromClosure = ClosureUtil::getRowsFromClosure(
      ClosureUtil::getReflectFunction( $closure )
    );

    $getRowsFromClosure = array_values( array_filter(
      $getRowsFromClosure, fn( string $row ) => (
        str_starts_with( trim( $row ), "use" )
      )
    ));

    ClosureUtil::$cacheUses[ spl_object_id( $closure )] = array_map(
      function( string $useItem ){
        $useItem = preg_replace([ "#^use\s*#", "#\n|\r#", "#;$#" ], [ "" ], $useItem);
        if( str_contains( $useItem, " as " ) === true ){
          $useItemAlias = end( explode( " as ", $useItem ));
          [ $useItemPath ] = explode( " as ", $useItem );
        } else {
          $useItemList = explode( "\\", $useItem );
          [ $useItemPath, $useItemAlias ] = array_merge(
            [ implode( "\\", array_slice( $useItemList, 0 ))], array_slice( $useItemList, -1, 1 )
          );
        } 

        return [ "path" => $useItemPath, "alias" => $useItemAlias ];
      }, $getRowsFromClosure );

    return ClosureUtil::$cacheUses[ spl_object_id( $closure )];
  }

  public static function getUse(
    Closure $closure,
    string $alias 
  ): string {
    [ $useItem ] = array_values(
      array_filter(
        ClosureUtil::getUses( ClosureUtil::getClosure( $closure )),
          fn( $use ) => $use[ "alias" ] === $alias
      )
    );
    
    return $useItem[ "path" ];
  }
  
  public static function createParam(
    Closure $closure,
    string $value
  ): string {
    if( isset( ClosureUtil::$cacheParams ) === false ){
      ClosureUtil::$cacheParams = [];
    }

    if( isset( ClosureUtil::$cacheParams[ spl_object_id( $closure )]) === false ){
      ClosureUtil::$cacheParams[ spl_object_id( $closure )] = [];
    }

    $paramKey = sprintf( ":param_%s", [ count( ClosureUtil::$cacheParams[ spl_object_id( $closure )])]);
    ClosureUtil::$cacheParams[ spl_object_id( $closure )][ $paramKey ] = $value;
    return $paramKey;
  }  
}