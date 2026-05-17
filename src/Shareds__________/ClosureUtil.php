<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\MetaType;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionFunction;
use Closure;

class ClosureUtil
{
  public static Collection $cacheEntityStructure;
  public static Collection $cacheReflectionFunction;
  public static Collection $cacheUses;
  public static Collection $cacheStatics;
  public static Collection $cacheParams;

  public static function getReflectFunction(
    Closure $closure
  ): ReflectionFunction {
    if( isset( ClosureUtil::$cacheReflectionFunction ) === false ){
      ClosureUtil::$cacheReflectionFunction = new Collection();
    }

    if( ClosureUtil::$cacheReflectionFunction->getOneOrFail( spl_object_id( $closure )) instanceof ReflectionFunction ){
      return ClosureUtil::$cacheReflectionFunction->getOneOrFail( spl_object_id( $closure ));
    }

    ClosureUtil::$cacheReflectionFunction->add( new ReflectionFunction( $closure ), spl_object_id( $closure ));
    return ClosureUtil::$cacheReflectionFunction->getOneOrFail( spl_object_id( $closure ));
  }

  public static function getRowsFromClosure(
    ReflectionFunction $cacheReflectionFunction  
  ): Collection {
    $getRowsFromClosure = new Collection(
      file( $cacheReflectionFunction->getFileName())
    );

    return $getRowsFromClosure->where(
      fn( string $row ) => !str_starts_with( trim( $row ), "//" )
    );
  }

  public static function getUses(
    Closure $closure  
  ): Uses {
    if( isset( ClosureUtil::$cacheUses ) === false ){
      ClosureUtil::$cacheUses = new Collection();
    }

    if( ClosureUtil::$cacheUses->getOneOrFail( spl_object_id( $closure )) instanceof Uses ){
      return ClosureUtil::$cacheUses->getOneOrFail( spl_object_id( $closure ));
    }

    $getRowsFromClosure = ClosureUtil::getRowsFromClosure(
      ClosureUtil::getReflectFunction( $closure )
    );

    ClosureUtil::$cacheUses->add( 
      new Uses( $getRowsFromClosure->where( 
        fn( string $row ) => str_starts_with( trim( $row ), "use" )
      )), spl_object_id( $closure )
    );

    return ClosureUtil::$cacheUses->getOneOrFail(
      spl_object_id( $closure )
    );
  }

  public static function getStatics(
    Closure $closure
  ): Collection {
    return new Collection(
      ClosureUtil::getReflectFunction(
        $closure
      )->getStaticVariables()
    );
  }

  public static function getEntityStructure(
    string $entity
  ): EntityStructure|null {
    if( isset( ClosureUtil::$cacheEntityStructure ) === false ){
      ClosureUtil::$cacheEntityStructure = new Collection();
    }

    if( class_exists( $entity )){
      if( ClosureUtil::$cacheEntityStructure->getOneOrFail( $entity ) instanceof EntityStructure ){
        return ClosureUtil::$cacheEntityStructure->getOneOrFail( $entity );
      }

      $entityStructure = Util::callUserFN(
        [ $entity, "meta" ], [ MetaType::Query ]
      );

      if( $entityStructure instanceof EntityStructure ){
        ClosureUtil::$cacheEntityStructure->add( $entityStructure, $entity );

        return $entityStructure;
      }
    }
    
    return null;
  }
  
  public static function scopeByVariable(
    Collection $scopes,
    string $variable
  ): Scope|null {
    $scopes = $scopes->where(
      fn( Scope $scope ) => $scope->variable === $variable
    );

    if( $scopes->exist() === false ){
      return null;
    }

    [ $scope ] = $scopes->toArray();
    if( $scope instanceof Scope ){
      return $scope;
    }

    return null;
  }

  public static function createParam(
    Closure $closure,
    string $value
  ): string {
    if( isset( ClosureUtil::$cacheParams ) === false ){
      ClosureUtil::$cacheParams = new Collection();
    }

    $paramsList = ClosureUtil::$cacheParams->getOneOrFail(
      spl_object_id( $closure )
    );

    if( $paramsList === null ){
      ClosureUtil::$cacheParams->add(
        Collection::create(), spl_object_id( $closure )
      );
    }

    $paramsList = ClosureUtil::$cacheParams->getOneOrFail( spl_object_id( $closure ));
    if( $paramsList instanceof Collection ){
      $paramKey = Util::sprintFormat(
        ":param_%s", [ $paramsList->count() ]
      );

      $paramsList->add( $value, $paramKey );
      return $paramKey;
    } else return $value;
  }
}