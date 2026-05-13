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
  public static Collection $reflectionFunction;
  public static Collection $cacheUses;
  public static Collection $cacheStatics;

  public static function getReflectFunction(
    Closure $closure
  ): ReflectionFunction {
    if( isset( ClosureUtil::$reflectionFunction ) === false ){
      ClosureUtil::$reflectionFunction = new Collection();
    }

    if( ClosureUtil::$reflectionFunction->getOneOrFail( spl_object_id( $closure )) instanceof ReflectionFunction ){
      return ClosureUtil::$reflectionFunction->getOneOrFail( spl_object_id( $closure ));
    }

    ClosureUtil::$reflectionFunction->add( new ReflectionFunction( $closure ), spl_object_id( $closure ));
    return ClosureUtil::$reflectionFunction->getOneOrFail( spl_object_id( $closure ));
  }

  public static function getRowsFromClosure(
    ReflectionFunction $reflectionFunction  
  ): Collection {
    $getRowsFromClosure = new Collection(
      file( $reflectionFunction->getFileName())
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
}