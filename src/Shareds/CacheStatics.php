<?php

namespace Websyspro\Entity\Shareds;

use ReflectionFunction;
use SplFileObject;
use function sprintf, array_slice;

define( "K_STATEMENTS", "statements" );
define( "K_VARIABLE", "variable" );

class CacheStatics
{
  private static array $cacheUseStatements = [];
  private static string $cacheDirectoryDefault = "Cache";

  private static function path(
    string|null $file = null
  ): string {
    $path = implode( DIRECTORY_SEPARATOR, [
      BASEDIR_APP, self::$cacheDirectoryDefault
    ]);
    
    return $file !== null 
      ? implode( DIRECTORY_SEPARATOR, [ $path, "{$file}.php" ])  
      : $path;
  }  

  public static function load(
    string $key    
  ): array|false {
    return @include self::path( $key );
  }

  public static function getUseStatements(
    ReflectionFunction $reflectionFunction     
  ): array {
    $keyHash = sprintf( 
      "use-statements-%s", md5(
        $reflectionFunction->getClosureScopeClass()
          ->getShortName()
      )
    );

    
    if( isset( self::$cacheUseStatements[ $keyHash ])){
      return self::$cacheUseStatements[ $keyHash ];
    }
    
    $handleCache = self::load( $keyHash );
    if( $handleCache !== false ){
      return $handleCache;
    }

    $handle = new SplFileObject(
      $reflectionFunction->getFileName()
    );

    while( $handle->eof() === false ){
      $handleFgets = $handle->fgets();
      self::$cacheUseStatements[ $keyHash ][] = trim( $handleFgets, "\r\n;" );
      if( str_contains( $handleFgets, "class" )){
        break;
      }
    }


    self::$cacheUseStatements[ $keyHash ] = array_filter( 
      self::$cacheUseStatements[ $keyHash ], fn( string $useStatements ) => (
        str_starts_with($useStatements, "use ")
      )
    );

    self::$cacheUseStatements[ $keyHash ] = array_map(
      function( string $useStatements ){
        $useStatements = str_replace( "use ", "", $useStatements );
        if( str_contains( $useStatements, "as" )){
          [ $statements, $alias ] = explode( "as", $useStatements );
          return [ K_STATEMENTS => $statements, K_VARIABLE => $alias ];
        } else {
          $statementsPaths = explode( "\\", $useStatements );
          [ $statements, $alias ] = [ $useStatements, ...array_slice( $statementsPaths, -1, 1 )];
          return [ K_STATEMENTS => $statements, K_VARIABLE => $alias ];
        }
      }, self::$cacheUseStatements[ $keyHash ]
    ); 

    return self::$cacheUseStatements[ $keyHash ];
  }
}