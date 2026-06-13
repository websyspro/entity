<?php

namespace Websyspro\Entity\Shareds;

use function sprintf;

class Cache
{
  private static array $cacheContexts = [];
  private const string CACHE_DIRETOCTORY_DEFAULT = "Cache";

  private static function path(
    string|null $file = null
  ): string {
    $path = implode( DIRECTORY_SEPARATOR, [
      BASEDIR_APP, self::CACHE_DIRETOCTORY_DEFAULT
    ]);
    
    return $file !== null 
      ? implode( DIRECTORY_SEPARATOR, [ $path, $file ])  
      : $path;
  }

  public static function file(
    string $file
  ): string {
    return self::path(
      "{$file}.php"
    );
  }

  public static function exist(
    string $file
  ): bool {
    if( CACHE_DISABLED === true ){
      return false;
    }

    if( isset( self::$cacheContexts[ $file ])){
      return true;
    }

    return file_exists(
      self::file( $file )
    );
  }
  
  public static function save(
    string $file,
    array $contexts
  ): array {
    file_put_contents( 
      self::path( "{$file}.php"), sprintf(
        "<?php%s%sreturn %s;", PHP_EOL, PHP_EOL, var_export($contexts, true)
      ), LOCK_EX
    );

    self::$cacheContexts[ $file ] = $contexts;
    return self::$cacheContexts[$file];
  }

  public static function delete(
    string $file
  ): void {
    unset( self::$cacheContexts[ $file ]);
    if( file_exists( self::file( $file ))){
      unlink( self::file( $file ));
    }
  }  
  
  public static function load(
    string $file
  ): array {
    if( isset( self::$cacheContexts[ $file ])){
      return self::$cacheContexts[ $file ];
    }

    self::$cacheContexts[ $file ] = require self::file( $file );
    return self::$cacheContexts[ $file ];
  }

  public static function entity(
    string $entity
  ): array {
    $hash = sprintf(
      "orm-entity-%s", md5( $entity )
    );

    if( self::exist( $hash ) ){
      return self::load( $hash );
    }

    $entityStructure = new EntityStructure( $entity );
    return self::save( $hash, $entityStructure->contexts );
  }
}